<?php

namespace Tests\Feature;

use App\Models\ApplicationQuestion;
use App\Models\JobApplication;
use App\Models\JobOpportunity;
use App\Models\JobPreferenceRule;
use App\Models\User;
use App\Services\Jobs\ApplicationApproval;
use App\Services\Jobs\ApplicationPreparationGuard;
use App\Services\Jobs\ImportDiscoveredJobs;
use App\Services\Jobs\SubmissionGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Program009Test extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_rejection_creates_rule_but_job_only_does_not(): void
    {
        [$owner, $job, $application] = $this->application();
        $this->actingAs($owner)->post(route('workspace.applications.decision', $application), [
            'action' => 'reject', 'rejection_reason' => 'remote_policy_mismatch', 'use_as_rule' => 1,
            'rule_severity' => 'hard_exclusion', 'rule_scope' => 'all_jobs', 'confirm_hard_rule' => 1,
        ])->assertRedirect(route('workspace.jobs.rejected'));
        $rule = JobPreferenceRule::firstOrFail();
        $this->assertSame('remote_policy', $rule->rule_type);
        $this->assertSame('hard_exclusion', $rule->severity);
        $this->assertNotNull($rule->source_review_history_id);

    }

    public function test_job_only_rejection_creates_no_reusable_rule(): void
    {
        [$owner, , $application] = $this->application();
        $this->actingAs($owner)->post(route('workspace.applications.decision', $application), [
            'action' => 'reject', 'rejection_reason' => 'weak_fit', 'use_as_rule' => 1,
            'rule_severity' => 'job_only', 'rule_scope' => 'all_jobs',
        ])->assertRedirect(route('workspace.jobs.rejected'));
        $this->assertDatabaseCount('job_preference_rules', 0);
    }

    public function test_hard_rule_requires_explicit_confirmation(): void
    {
        [$owner, $job, $application] = $this->application();
        $this->actingAs($owner)->from(route('workspace.applications.show', $application))->post(route('workspace.applications.decision', $application), [
            'action' => 'reject', 'rejection_reason' => 'remote_policy_mismatch', 'use_as_rule' => 1,
            'rule_severity' => 'hard_exclusion', 'rule_scope' => 'all_jobs',
        ])->assertSessionHasErrors('confirm_hard_rule');
        $this->assertSame('approved_for_preparation', $job->fresh()->review_status);
        $this->assertDatabaseCount('job_preference_rules', 0);
    }

    public function test_ovoko_limited_remote_rule_blocks_similar_jobs_but_not_fully_remote(): void
    {
        $owner = $this->owner();
        $this->rule($owner, 'hard_exclusion');
        $service = app(ImportDiscoveredJobs::class);
        $limited = $service->import($owner, [$this->record('limited', 'Remote work limited to 30 days per year')], true);
        $fully = $service->import($owner, [$this->record('full', 'Fully remote across EMEA')], true);
        $this->assertSame(1, $limited['excluded']);
        $this->assertSame('excluded', $limited['items'][0]['decision']);
        $this->assertStringContainsString('Excluded by confirmed remote_policy rule', $limited['items'][0]['evaluations'][0]['explanation']);
        $this->assertSame(0, $fully['excluded']);
        $this->assertSame('would_create', $fully['items'][0]['status']);
    }

    public function test_penalties_adjust_final_score_and_inactive_expired_rules_do_not_apply(): void
    {
        $owner = $this->owner();
        $this->rule($owner, 'soft_penalty');
        $inactive = $this->rule($owner, 'strong_penalty');
        $inactive->update(['active' => false]);
        $expired = $this->rule($owner, 'strong_penalty');
        $expired->update(['expires_at' => now()->subMinute()]);
        $report = app(ImportDiscoveredJobs::class)->import($owner, [$this->record('score', 'Hybrid role', 90)], true);
        $this->assertSame(-8, $report['items'][0]['preference_adjustment']);
        $this->assertSame(82, $report['items'][0]['final_fit_score']);
    }

    public function test_rule_scope_is_respected_and_evaluations_persist_for_imported_jobs(): void
    {
        $owner = $this->owner();
        $rule = $this->rule($owner, 'soft_penalty');
        $rule->update(['scope' => 'company', 'comparison_value_json' => ['values' => ['hybrid'], 'scope_value' => 'Matched Ltd']]);
        $records = [$this->record('other', 'Hybrid role', 80), [...$this->record('matched', 'Hybrid role', 80), 'company_name' => 'Matched Ltd']];
        $report = app(ImportDiscoveredJobs::class)->import($owner, $records);
        $this->assertSame(2, $report['created']);
        $this->assertSame(0, JobOpportunity::where('company_name', 'Synthetic Ltd')->value('preference_adjustment'));
        $this->assertSame(-8, JobOpportunity::where('company_name', 'Matched Ltd')->value('preference_adjustment'));
        $this->assertDatabaseCount('job_rule_evaluations', 1);
    }

    public function test_submission_guard_requires_current_approval_and_stops_runtime_challenges(): void
    {
        [$owner, , $application] = $this->application();
        $application->forceFill(['status' => 'ready_for_final_review', 'application_answers' => ['name' => 'Synthetic Candidate']])->save();
        app(ApplicationApproval::class)->approve($application, $owner, null);
        $this->assertTrue(app(SubmissionGuard::class)->ready($application->fresh()));
        $blockers = app(SubmissionGuard::class)->blockers($application->fresh(), ['captcha' => true, 'mfa' => true, 'ambiguous_legal_consent' => true]);
        $this->assertCount(3, $blockers);
        $application->application_answers = ['name' => 'Changed'];
        $application->save();
        $this->assertFalse(app(SubmissionGuard::class)->ready($application->fresh()));

        ApplicationQuestion::create(['job_application_id' => $application->id, 'question' => 'I certify...', 'legal_or_sensitive' => true, 'requires_user_confirmation' => true]);
        $this->assertContains('A legal or sensitive declaration remains unresolved.', app(SubmissionGuard::class)->blockers($application->fresh()));
    }

    public function test_preparation_requires_approved_open_eligible_job_and_complete_package(): void
    {
        [, $job, $application] = $this->application();
        $guard = app(ApplicationPreparationGuard::class);
        $this->assertFalse($guard->readyForFinalReview($application));
        $job->location_eligibility = 'North Macedonia eligible';
        $job->save();
        $application->fill(['tailored_cv_path' => 'private/cv.pdf', 'cover_letter_path' => 'private/letter.pdf', 'application_answers' => ['name' => 'Synthetic Candidate'], 'salary_answer' => 'Within approved range', 'preparation_notes' => 'Synthetic role research and interview preparation.'])->save();
        $this->assertTrue($guard->readyForFinalReview($application->fresh()));
        $job->review_status = 'rejected';
        $job->save();
        $this->assertFalse($guard->readyForFinalReview($application->fresh()));
    }

    public function test_preferences_are_private_owner_scoped_and_absent_from_sitemap(): void
    {
        $owner = $this->owner();
        $rule = $this->rule($owner, 'soft_penalty');
        $other = User::create(['name' => 'other', 'email' => 'other@example.test', 'email_verified_at' => now(), 'password' => Hash::make('secret-pass')]);
        $this->get(route('workspace.preferences.index'))->assertRedirect(route('workspace.login'));
        $this->actingAs($other)->patch(route('workspace.preferences.update', $rule), ['severity' => 'soft_penalty'])->assertForbidden();
        $this->get(route('sitemap'))->assertOk()->assertDontSee('/workspace/preferences');
    }

    private function rule(User $owner, string $severity): JobPreferenceRule
    {
        $rule = new JobPreferenceRule(['rule_type' => 'remote_policy', 'rule_key' => 'annual_remote_limit', 'operator' => 'contains_any', 'comparison_value_json' => ['values' => ['hybrid', 'days per year']], 'severity' => $severity, 'scope' => 'all_jobs', 'reason' => 'Limited remote work is incompatible.', 'active' => true, 'confirmed_at' => now()]);
        $rule->user_id = $owner->id;
        $rule->save();

        return $rule;
    }

    private function record(string $id, string $remote, int $score = 85): array
    {
        return ['company_name' => 'Synthetic Ltd', 'role_title' => 'Senior Laravel Developer', 'original_url' => "https://jobs.example.test/{$id}", 'source' => 'employer', 'external_job_id' => $id, 'remote_scope' => $remote, 'location_eligibility' => 'North Macedonia eligible', 'fit_score' => $score];
    }

    private function application(string $prefix = 'owner'): array
    {
        $owner = $this->owner($prefix);
        $url = "https://jobs.example.test/{$prefix}";
        $job = new JobOpportunity(['company_name' => 'Synthetic', 'role_title' => 'Senior Developer', 'original_url' => $url, 'normalized_url' => $url, 'normalized_url_hash' => hash('sha256', $url), 'source' => 'employer', 'discovered_at' => now(), 'source_status' => 'verified_open', 'review_status' => 'approved_for_preparation']);
        $job->user_id = $owner->id;
        $job->save();
        $application = new JobApplication(['status' => 'preparing_application', 'final_application_url' => $url]);
        $application->user_id = $owner->id;
        $application->job_opportunity_id = $job->id;
        $application->save();

        return [$owner, $job, $application];
    }

    private function owner(string $prefix = 'owner'): User
    {
        $user = User::create(['name' => $prefix, 'email' => "{$prefix}@example.test", 'email_verified_at' => now(), 'password' => Hash::make('secret-pass')]);
        $user->is_workspace_owner = true;
        $user->save();

        return $user;
    }
}
