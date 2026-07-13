<?php

namespace Tests\Feature;

use App\Models\ApplicationQuestion;
use App\Models\CandidateProfile;
use App\Models\JobApplication;
use App\Models\JobOpportunity;
use App\Models\User;
use App\Services\Jobs\ApplicationApproval;
use App\Services\Workspace\StateTransitions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Program008Test extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        $user = User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'email_verified_at' => now(), 'password' => Hash::make('secret-pass')]);
        $user->is_workspace_owner = true;
        $user->save();

        return $user;
    }

    public function test_workspace_is_private_and_not_publicly_discoverable(): void
    {
        $this->get('/workspace/jobs')->assertRedirect('/workspace/login');
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('workspace')->assertDontSee('jobs');
        $this->get('/robots.txt')->assertOk()->assertDontSee('/workspace/jobs');
    }

    public function test_owner_cannot_access_another_users_job(): void
    {
        $owner = $this->user();
        $other = User::create(['name' => 'Other', 'email' => 'other@example.test', 'email_verified_at' => now(), 'password' => 'x']);
        $job = $this->job($other);
        $this->actingAs($owner)->patch(route('workspace.jobs.review', $job), ['action' => 'saved_for_later'])->assertNotFound();
        $this->actingAs($other)->get(route('workspace.jobs.index'))->assertForbidden();
    }

    public function test_approval_for_preparation_does_not_approve_submission(): void
    {
        $u = $this->user();
        $job = $this->job($u);
        $this->actingAs($u)->patch(route('workspace.jobs.review', $job), ['action' => 'approved_for_preparation'])->assertRedirect();
        $this->assertDatabaseHas('job_applications', ['job_opportunity_id' => $job->id, 'status' => 'preparing_application', 'approved_at' => null]);
    }

    public function test_final_approval_requires_resolved_questions_and_hashes_exact_content(): void
    {
        $u = $this->user();
        $job = $this->job($u);
        $app = new JobApplication(['status' => 'ready_for_final_review', 'final_application_url' => $job->original_url, 'application_answers' => ['why' => 'Because']]);
        $app->user_id = $u->id;
        $app->job_opportunity_id = $job->id;
        $app->save();
        ApplicationQuestion::create(['job_application_id' => $app->id, 'question' => 'Work authorization?', 'requires_user_confirmation' => true, 'legal_or_sensitive' => true]);
        $this->actingAs($u)->post(route('workspace.applications.approve', $app))->assertStatus(422);
        $question = $app->questions()->first();
        $question->answer = 'Confirmed by user';
        $question->confirmed_at = now();
        $question->confirmed_by = $u->id;
        $question->save();
        $this->actingAs($u)->post(route('workspace.applications.approve', $app), ['approval_confirmation' => '1'])->assertRedirect();
        $app->refresh();
        $this->assertSame('approved_for_submission', $app->status);
        $this->assertNotNull($app->approved_application_hash);
        $this->assertTrue(app(ApplicationApproval::class)->isCurrent($app));
        $app->application_answers = ['why' => 'Changed after approval'];
        $app->save();
        $this->assertFalse(app(ApplicationApproval::class)->isCurrent($app));
    }

    public function test_import_command_dry_run_normalizes_and_does_not_write(): void
    {
        $u = $this->user();
        $path = tempnam(sys_get_temp_dir(), 'jobs');
        file_put_contents($path, json_encode([['company_name' => 'Acme', 'role_title' => 'Engineer', 'original_url' => 'http://EXAMPLE.com/jobs/1/?utm_source=x', 'source' => 'manual']]));
        $this->artisan('jobs:import-discovered', ['json-file' => $path, '--user' => $u->email, '--dry-run' => true])->assertSuccessful()->expectsOutputToContain('would_create');
        $this->assertDatabaseCount('job_opportunities', 0);
        unlink($path);
    }

    public function test_import_stores_a_fixed_length_normalized_url_hash(): void
    {
        $user = $this->user();
        $path = storage_path('framework/testing/import-hash.json');
        file_put_contents($path, json_encode([['company_name' => 'Acme', 'role_title' => 'Engineer', 'original_url' => 'https://example.com/jobs/1?utm_source=test', 'source' => 'manual']]));

        $this->artisan('jobs:import-discovered', ['json-file' => $path, '--user' => $user->email])->assertSuccessful();

        $job = JobOpportunity::sole();
        $this->assertSame(hash('sha256', 'https://example.com/jobs/1'), $job->normalized_url_hash);
        $this->assertSame(64, strlen($job->normalized_url_hash));
        unlink($path);
    }

    public function test_sensitive_profile_values_are_encrypted_at_rest(): void
    {
        $user = $this->user();
        $profile = new CandidateProfile(['full_name' => 'Synthetic Reviewer', 'professional_email' => 'private@example.test', 'phone' => '+389 70 000 000', 'salary_target' => 90000]);
        $profile->user_id = $user->id;
        $profile->save();
        $raw = DB::table('candidate_profiles')->where('id', $profile->id)->first();
        $this->assertNotSame('private@example.test', $raw->professional_email);
        $this->assertNotSame('+389 70 000 000', $raw->phone);
        $this->assertNotSame('90000', $raw->salary_target);
    }

    public function test_invalid_state_transitions_are_rejected(): void
    {
        $job = $this->job($this->user());
        $job->review_status = 'expired';
        $this->expectException(ValidationException::class);
        app(StateTransitions::class)->job($job, 'approved_for_preparation');
    }

    public function test_interactive_user_creation_refuses_unsafe_password(): void
    {
        $this->artisan('workspace:user-create')
            ->expectsQuestion('Full name', 'Synthetic Owner')
            ->expectsQuestion('Email', 'owner@example.test')
            ->expectsQuestion('Password (minimum 16 characters, mixed case, number, symbol)', 'password')
            ->expectsQuestion('Confirm password', 'password')
            ->expectsOutput('Unsafe or invalid credentials refused.')
            ->assertExitCode(2);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_every_private_view_uses_the_shared_shell_and_vite_asset(): void
    {
        $user = $this->user();
        $job = $this->job($user);
        $application = new JobApplication(['status' => 'preparing_application', 'final_application_url' => $job->original_url]);
        $application->user_id = $user->id;
        $application->job_opportunity_id = $job->id;
        $application->save();
        foreach ([route('workspace.dashboard'), route('workspace.jobs.index'), route('workspace.jobs.show', $job), route('workspace.profile.show'), route('workspace.applications.show', $application)] as $url) {
            $this->actingAs($user)->get($url)->assertOk()->assertSee('Private workspace')->assertSee('/build/assets/workspace-')->assertSee('Skip to workspace content');
        }
        auth()->logout();
        $this->get(route('workspace.login'))->assertOk()->assertSee('Private job workspace')->assertSee('/build/assets/workspace-');
        $this->get(route('home'))->assertOk()->assertDontSee('Job inbox')->assertDontSee('Candidate profile');
    }

    public function test_workspace_components_include_mobile_safe_patterns(): void
    {
        $css = file_get_contents(resource_path('css/workspace.css'));

        $this->assertStringContainsString('@media(max-width:620px)', $css);
        $this->assertStringContainsString('.field-row{grid-template-columns:1fr', $css);
        $this->assertStringContainsString('.button{width:100%', $css);
        $this->assertStringContainsString('.sticky-action-bar{position:static}', $css);
        $this->assertStringContainsString('scrollbar-width:thin', $css);
        $this->assertStringContainsString('scroll-padding-inline:12px', $css);
        $workspaceJavascript = file_get_contents(resource_path('js/workspace.js'));
        $this->assertStringContainsString("addEventListener('focusin'", $workspaceJavascript);
        $this->assertStringContainsString("querySelector('.is-current')", $workspaceJavascript);
        foreach (['action-bar', 'breadcrumb', 'button', 'callout', 'card', 'data-list', 'empty-value', 'field-row', 'status-badge', 'validation-summary'] as $component) {
            $this->assertFileExists(resource_path("views/components/workspace/{$component}.blade.php"));
        }
    }

    public function test_final_review_safety_state_is_human_readable_and_blocks_unresolved_approval(): void
    {
        $user = $this->user();
        $job = $this->job($user);
        $application = new JobApplication(['status' => 'ready_for_final_review', 'final_application_url' => $job->original_url]);
        $application->user_id = $user->id;
        $application->job_opportunity_id = $job->id;
        $application->save();
        ApplicationQuestion::create(['job_application_id' => $application->id, 'question' => 'Legal declaration?', 'requires_user_confirmation' => true, 'legal_or_sensitive' => true]);
        $response = $this->actingAs($user)->get(route('workspace.applications.show', $application));
        $response->assertOk()->assertSee('Approving authorizes the exact displayed application package for submission. Any later change invalidates approval.')
            ->assertSee('Required questions still need user confirmation.')->assertSee('No answers prepared')->assertSee('No document selected')
            ->assertSee('Resolve blockers before approval')->assertSee('approval_confirmation')->assertSee('required', false)
            ->assertSee('disabled', false)->assertDontSee('ready_for_final_review')->assertDontSee('null');
    }

    public function test_validation_errors_render_inside_private_layout(): void
    {
        $user = $this->user();
        $this->actingAs($user)->from(route('workspace.profile.show'))->put(route('workspace.profile.update'), [])->assertRedirect(route('workspace.profile.show'));
        $this->actingAs($user)->get(route('workspace.profile.show'))->assertOk()->assertSee('Please review the highlighted information')->assertSee('Private workspace');
    }

    private function job(User $u): JobOpportunity
    {
        $job = new JobOpportunity(['company_name' => 'Acme', 'role_title' => 'Engineer', 'original_url' => 'https://example.com/jobs/1', 'normalized_url' => 'https://example.com/jobs/1', 'source' => 'manual', 'discovered_at' => now(), 'source_status' => 'verified_open', 'review_status' => 'needs_review']);
        $job->user_id = $u->id;
        $job->save();

        return $job;
    }
}
