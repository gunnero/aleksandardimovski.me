<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobOpportunity;
use App\Models\OpportunityReviewHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Program0083Test extends TestCase
{
    use RefreshDatabase;

    public function test_owner_rejects_approved_opportunity_and_withdraws_linked_application(): void
    {
        [$owner, $job, $application] = $this->approvedApplication();
        OpportunityReviewHistory::create([
            'job_opportunity_id' => $job->id, 'reviewed_by' => $owner->id, 'old_status' => 'needs_review',
            'new_status' => 'approved_for_preparation', 'action' => 'decision', 'reviewed_at' => now()->subHour(),
        ]);
        $document = "job-applications/{$application->id}/11111111-1111-1111-1111-111111111111.pdf";
        Storage::disk('local')->put($document, 'private synthetic document');

        $response = $this->actingAs($owner)->post(route('workspace.applications.decision', $application), [
            'action' => 'reject', 'rejection_reason' => 'remote_policy_mismatch',
            'note' => 'Remote work is limited to 30 days per year, which does not match my fully remote requirement.',
        ]);

        $response->assertRedirect(route('workspace.jobs.rejected'))->assertSessionHas('status');
        $job->refresh();
        $application->refresh();
        $this->assertSame('rejected', $job->review_status);
        $this->assertSame('withdrawn', $application->status);
        $this->assertSame('remote_policy_mismatch', $job->rejection_reason);
        $this->assertSame('remote_policy_mismatch', $application->rejection_reason);
        $this->assertSame($owner->id, $job->reviewed_by);
        $this->assertSame($owner->id, $application->rejected_by);
        $this->assertNotNull($job->reviewed_at);
        $this->assertNotNull($application->rejected_at);
        $this->assertTrue(Storage::disk('local')->exists($document));

        $history = $job->reviewHistory()->latest('id')->firstOrFail();
        $this->assertSame('approved_for_preparation', $history->old_status);
        $this->assertSame('rejected', $history->new_status);
        $this->assertSame('candidate_rejection', $history->action);
        $this->assertSame('remote_policy_mismatch', $history->rejection_reason);
        $this->assertSame(2, $job->reviewHistory()->count());
        $this->assertNotSame($history->review_note, DB::table('opportunity_review_histories')->where('id', $history->id)->value('review_note'));
        $this->assertNotSame($application->rejection_note, DB::table('job_applications')->where('id', $application->id)->value('rejection_note'));
    }

    public function test_rejection_invalidates_approvals_moves_views_and_prevents_submission(): void
    {
        [$owner, $job, $application] = $this->approvedApplication('ready_for_final_review');

        $this->actingAs($owner)->post(route('workspace.applications.decision', $application), [
            'action' => 'reject', 'rejection_reason' => 'remote_policy_mismatch',
        ])->assertRedirect(route('workspace.jobs.rejected'));

        $application->refresh();
        foreach (['approved_by', 'approved_at', 'approved_application_hash', 'approved_document_hashes', 'submitted_document_hashes', 'submitted_answers_hash'] as $field) {
            $this->assertNull($application->{$field});
        }
        $this->actingAs($owner)->get(route('workspace.jobs.approved'))->assertDontSee($job->role_title);
        $this->actingAs($owner)->get(route('workspace.jobs.rejected'))->assertSee($job->role_title);
        $this->actingAs($owner)->get(route('workspace.dashboard'))->assertSee($job->role_title)->assertSee('Withdrawn');
        $this->actingAs($owner)->get(route('workspace.applications.show', $application))->assertOk()
            ->assertSee('Application closed by candidate')->assertSee('Remote policy does not match requirements')
            ->assertDontSee('Approve exact package for submission')->assertDontSee('Reject and close application');
        $this->actingAs($owner)->post(route('workspace.applications.approve', $application), ['approval_confirmation' => '1'])->assertStatus(422);
        $this->assertDatabaseCount('job_applications', 1);
    }

    public function test_rejection_reason_is_required_and_return_does_not_attempt_no_op_transition(): void
    {
        [$owner, $job, $application] = $this->approvedApplication();

        $this->actingAs($owner)->from(route('workspace.applications.show', $application))
            ->post(route('workspace.applications.decision', $application), ['action' => 'reject'])
            ->assertRedirect(route('workspace.applications.show', $application))->assertSessionHasErrors('rejection_reason');
        $this->assertSame('approved_for_preparation', $job->fresh()->review_status);
        $this->assertSame('preparing_application', $application->fresh()->status);

        $this->actingAs($owner)->post(route('workspace.applications.decision', $application), [
            'action' => 'request_changes', 'note' => 'Keep preparing this application.',
        ])->assertRedirect();
        $this->assertSame('preparing_application', $application->fresh()->status);
        $this->assertSame('Keep preparing this application.', $application->fresh()->preparation_notes);
    }

    public function test_rejection_form_has_csrf_and_owner_authorization(): void
    {
        [$owner, $job, $application] = $this->approvedApplication();
        $other = User::create(['name' => 'Other', 'email' => 'other@example.test', 'email_verified_at' => now(), 'password' => Hash::make('secret-pass')]);

        $this->get(route('workspace.applications.show', $application))->assertRedirect(route('workspace.login'));
        $this->actingAs($other)->post(route('workspace.applications.decision', $application), [
            'action' => 'reject', 'rejection_reason' => 'remote_policy_mismatch',
        ])->assertForbidden();
        $this->actingAs($owner)->get(route('workspace.applications.show', $application))->assertOk()
            ->assertSee('name="_token"', false)->assertSee('name="rejection_reason"', false)
            ->assertSee('Remote policy does not match requirements')->assertSee('required', false);
        $this->assertSame('approved_for_preparation', $job->fresh()->review_status);
    }

    private function approvedApplication(string $applicationStatus = 'preparing_application'): array
    {
        $owner = $this->owner();
        $job = new JobOpportunity([
            'company_name' => 'Synthetic Company', 'role_title' => 'Remote Platform Engineer',
            'original_url' => 'https://example.com/jobs/008-3', 'normalized_url' => 'https://example.com/jobs/008-3',
            'normalized_url_hash' => hash('sha256', 'https://example.com/jobs/008-3'), 'source' => 'employer',
            'discovered_at' => now(), 'source_status' => 'verified_open', 'review_status' => 'approved_for_preparation',
        ]);
        $job->user_id = $owner->id;
        $job->save();
        $application = new JobApplication(['status' => $applicationStatus, 'final_application_url' => $job->original_url]);
        $application->user_id = $owner->id;
        $application->job_opportunity_id = $job->id;
        $application->save();
        $application->forceFill([
            'approved_by' => $owner->id, 'approved_at' => now(), 'approved_application_hash' => 'approved-hash',
            'approved_document_hashes' => ['private.pdf' => 'hash'], 'submitted_document_hashes' => ['private.pdf' => 'submitted-hash'],
            'submitted_answers_hash' => 'submitted-answers-hash',
        ])->save();

        return [$owner, $job, $application];
    }

    private function owner(string $prefix = 'owner'): User
    {
        $user = User::create(['name' => ucfirst($prefix), 'email' => $prefix.'@example.test', 'email_verified_at' => now(), 'password' => Hash::make('secret-pass')]);
        $user->is_workspace_owner = true;
        $user->save();

        return $user;
    }
}
