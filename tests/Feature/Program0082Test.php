<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobOpportunity;
use App\Models\OpportunityReviewHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Program0082Test extends TestCase
{
    use RefreshDatabase;

    public function test_inbox_contains_only_pending_review_statuses(): void
    {
        $user = $this->owner();
        foreach (['discovered', 'needs_review', 'approved_for_preparation', 'rejected', 'saved_for_later', 'needs_research', 'duplicate', 'expired'] as $status) {
            $this->job($user, $status);
        }

        $response = $this->actingAs($user)->get(route('workspace.jobs.index'));
        $response->assertOk()->assertSee('Role discovered')->assertSee('Role needs review')
            ->assertDontSee('Role approved for preparation')->assertDontSee('Role rejected')
            ->assertDontSee('Role saved for later')->assertDontSee('Role needs research')
            ->assertDontSee('Role duplicate')->assertDontSee('Role expired');
    }

    public function test_decision_moves_job_updates_dashboard_and_records_actor_and_encrypted_history(): void
    {
        $user = $this->owner();
        $job = $this->job($user, 'needs_review');

        $this->actingAs($user)->patch(route('workspace.jobs.review', $job), ['action' => 'saved_for_later', 'note' => 'Review again after the next quarter.'])
            ->assertRedirect(route('workspace.jobs.saved'))->assertSessionHas('status');

        $job->refresh();
        $this->assertSame('saved_for_later', $job->review_status);
        $this->assertSame($user->id, $job->reviewed_by);
        $this->assertNotNull($job->reviewed_at);
        $this->actingAs($user)->get(route('workspace.jobs.index'))->assertDontSee($job->role_title);
        $this->actingAs($user)->get(route('workspace.jobs.saved'))->assertSee($job->role_title)->assertSee('Review again after the next quarter.');
        $this->actingAs($user)->get(route('workspace.dashboard'))->assertSee('Saved for later')->assertSee('New jobs to review');
        $history = OpportunityReviewHistory::sole();
        $this->assertSame('needs_review', $history->old_status);
        $this->assertSame('saved_for_later', $history->new_status);
        $this->assertNotSame('Review again after the next quarter.', DB::table('opportunity_review_histories')->value('review_note'));
    }

    public function test_approval_creates_exactly_one_application_and_approved_page_links_it(): void
    {
        $user = $this->owner();
        $job = $this->job($user, 'needs_review');
        $this->actingAs($user)->patch(route('workspace.jobs.review', $job), ['action' => 'approved_for_preparation'])->assertRedirect(route('workspace.jobs.approved'));
        $application = JobApplication::sole();
        $this->actingAs($user)->get(route('workspace.jobs.approved'))->assertOk()->assertSee($job->role_title)->assertSee(route('workspace.applications.show', $application));
        $this->actingAs($user)->patch(route('workspace.jobs.review', $job), ['action' => 'approved_for_preparation'])->assertSessionHasErrors('status');
        $this->assertDatabaseCount('job_applications', 1);
    }

    public function test_restore_supported_statuses_preserves_history_and_creates_no_application(): void
    {
        $user = $this->owner();
        foreach (['rejected', 'saved_for_later', 'needs_research', 'duplicate'] as $status) {
            $job = $this->job($user, $status);
            OpportunityReviewHistory::create(['job_opportunity_id' => $job->id, 'reviewed_by' => $user->id, 'old_status' => 'needs_review', 'new_status' => $status, 'review_note' => 'Prior note', 'action' => 'decision', 'reviewed_at' => now()->subMinute()]);
            $this->actingAs($user)->patch(route('workspace.jobs.restore', $job))->assertRedirect(route('workspace.jobs.index'));
            $this->assertSame('needs_review', $job->fresh()->review_status);
            $this->assertSame(2, $job->reviewHistory()->count());
        }
        $this->assertDatabaseCount('job_applications', 0);
    }

    public function test_expired_restore_requires_fresh_open_verification(): void
    {
        $user = $this->owner();
        $job = $this->job($user, 'expired');
        $job->forceFill(['reviewed_at' => now(), 'source_status' => 'expired'])->save();
        $this->actingAs($user)->patch(route('workspace.jobs.restore', $job))->assertSessionHasErrors('status');
        $job->forceFill(['source_status' => 'verified_open', 'source_verified_at' => now()->addMinute(), 'application_deadline' => now()->addMonth()])->save();
        $this->actingAs($user)->patch(route('workspace.jobs.restore', $job))->assertRedirect(route('workspace.jobs.index'));
        $this->assertSame('needs_review', $job->fresh()->review_status);
    }

    public function test_status_pages_are_private_owner_only_and_human_readable(): void
    {
        $owner = $this->owner();
        $other = User::create(['name' => 'Other', 'email' => 'other@example.test', 'email_verified_at' => now(), 'password' => Hash::make('secret')]);
        foreach (['approved', 'rejected', 'saved', 'research', 'duplicates', 'expired', 'all'] as $route) {
            $url = route('workspace.jobs.'.$route);
            auth()->logout();
            $this->get($url)->assertRedirect(route('workspace.login'));
            $this->actingAs($other)->get($url)->assertForbidden();
            $this->actingAs($owner)->get($url)->assertOk()->assertDontSeeText('approved_for_preparation')->assertDontSeeText('needs_research')->assertDontSeeText('null');
        }
    }

    public function test_all_view_filters_sorts_and_paginates_with_query_string(): void
    {
        $user = $this->owner();
        foreach (range(1, 16) as $index) {
            $this->job($user, 'needs_review', 'Company '.str_pad((string) $index, 2, '0', STR_PAD_LEFT));
        }
        $this->actingAs($user)->get(route('workspace.jobs.all', ['status' => 'needs_review', 'sort' => 'company', 'q' => 'Company']))
            ->assertOk()->assertSee('Company 01')->assertDontSee('Company 16')->assertSee('page=2', false);
    }

    public function test_empty_inbox_and_mobile_safe_status_navigation_are_present(): void
    {
        $user = $this->owner();
        $this->actingAs($user)->get(route('workspace.jobs.index'))->assertOk()->assertSee('Inbox clear')->assertSee('No opportunities are currently waiting for review.');
        $css = file_get_contents(resource_path('css/workspace.css'));
        $this->assertStringContainsString('.status-subnav{display:flex', $css);
        $this->assertStringContainsString('overflow-x:auto', $css);
        $this->assertStringContainsString('.metric-grid--status,.advanced-filters__grid{grid-template-columns:1fr}', $css);
        $this->assertStringContainsString('form[data-confirm]', file_get_contents(resource_path('js/workspace.js')));
    }

    private function owner(string $suffix = ''): User
    {
        $user = User::create(['name' => 'Owner', 'email' => 'owner'.$suffix.'@example.test', 'email_verified_at' => now(), 'password' => Hash::make('secret-pass')]);
        $user->is_workspace_owner = true;
        $user->save();

        return $user;
    }

    private function job(User $user, string $status, ?string $company = null): JobOpportunity
    {
        static $sequence = 0;
        $sequence++;
        $job = new JobOpportunity(['company_name' => $company ?? 'Company '.$sequence, 'role_title' => 'Role '.str_replace('_', ' ', $status), 'original_url' => 'https://example.com/jobs/'.$sequence, 'normalized_url' => 'https://example.com/jobs/'.$sequence, 'normalized_url_hash' => hash('sha256', 'https://example.com/jobs/'.$sequence), 'source' => 'employer', 'discovered_at' => now(), 'source_status' => 'verified_open', 'review_status' => $status, 'fit_score' => 80]);
        $job->user_id = $user->id;
        $job->save();

        return $job;
    }
}
