<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\AgentActivity;
use App\Models\JobApplication;
use App\Models\OpportunityReviewHistory;
use App\Services\Jobs\ApplicationApproval;
use App\Services\Workspace\StateTransitions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function show(Request $r, JobApplication $application)
    {
        $this->owned($r, $application);
        $application->load('opportunity', 'questions', 'accountTasks');
        $blockedReasons = [];
        if ($application->questions->where('requires_user_confirmation', true)->whereNull('confirmed_at')->isNotEmpty()) {
            $blockedReasons[] = 'Required questions still need user confirmation.';
        }
        if (! filter_var($application->final_application_url, FILTER_VALIDATE_URL)) {
            $blockedReasons[] = 'A valid final application URL is required.';
        }
        if (! in_array($application->opportunity->source_status, ['open', 'verified_open'], true)) {
            $blockedReasons[] = 'The source posting is not verified open.';
        }
        if ($application->opportunity->application_deadline?->isPast()) {
            $blockedReasons[] = 'The application deadline has passed.';
        }

        return view('workspace.applications.show', compact('application', 'blockedReasons'));
    }

    public function approve(Request $r, JobApplication $application, ApplicationApproval $approval, StateTransitions $transitions)
    {
        $this->owned($r, $application);
        abort_unless($application->status === 'ready_for_final_review', 422);
        $transitions->application($application, 'approved_for_submission');
        abort_if($application->questions()->where('requires_user_confirmation', true)->whereNull('confirmed_at')->exists(), 422, 'Required questions remain unresolved.');
        abort_unless(filter_var($application->final_application_url, FILTER_VALIDATE_URL), 422, 'A valid application URL is required.');
        abort_unless(in_array($application->opportunity->source_status, ['open', 'verified_open'], true), 422, 'The job must be verified open.');
        abort_if($application->opportunity->application_deadline?->isPast(), 422, 'The application deadline has passed.');
        $data = $r->validate(['approval_confirmation' => 'accepted', 'approval_note' => 'nullable|string|max:2000']);
        $approval->approve($application, $r->user(), $data['approval_note'] ?? null);

        return back()->with('status', 'Submission approved for the exact content shown.');
    }

    public function decision(Request $r, JobApplication $application, StateTransitions $transitions)
    {
        $this->owned($r, $application);
        $d = $r->validate([
            'action' => 'required|in:request_changes,reject,return_to_preparation',
            'rejection_reason' => 'nullable|required_if:action,reject|in:remote_policy_mismatch,compensation,location,weak_fit,company_concern,unrelated_stack,other',
            'note' => 'nullable|string|max:2000',
        ]);
        if ($d['action'] === 'reject') {
            DB::transaction(function () use ($r, $application, $transitions, $d): void {
                $application->loadMissing('opportunity');
                $opportunity = $application->opportunity;
                $oldJobStatus = $opportunity->review_status;
                $oldApplicationStatus = $application->status;
                $newApplicationStatus = in_array($oldApplicationStatus, ['withdrawn', 'closed'], true) ? $oldApplicationStatus : 'withdrawn';
                $transitions->job($opportunity, 'rejected');
                if ($newApplicationStatus !== $oldApplicationStatus) {
                    $transitions->application($application, $newApplicationStatus);
                }
                $rejectedAt = now();
                $opportunity->forceFill([
                    'review_status' => 'rejected', 'rejection_reason' => $d['rejection_reason'], 'review_notes' => $d['note'] ?? null,
                    'reviewed_by' => $r->user()->id, 'reviewed_at' => $rejectedAt,
                ])->save();
                $application->forceFill([
                    'status' => $newApplicationStatus, 'rejection_reason' => $d['rejection_reason'], 'rejection_note' => $d['note'] ?? null,
                    'rejected_by' => $r->user()->id, 'rejected_at' => $rejectedAt,
                    'approved_by' => null, 'approved_at' => null, 'approved_application_hash' => null, 'approved_document_hashes' => null,
                    'submitted_document_hashes' => null, 'submitted_answers_hash' => null,
                ])->save();
                OpportunityReviewHistory::create([
                    'job_opportunity_id' => $opportunity->id, 'reviewed_by' => $r->user()->id,
                    'old_status' => $oldJobStatus, 'new_status' => 'rejected', 'rejection_reason' => $d['rejection_reason'],
                    'review_note' => $d['note'] ?? null, 'action' => 'candidate_rejection', 'reviewed_at' => $rejectedAt,
                ]);
                AgentActivity::create([
                    'user_id' => $r->user()->id, 'job_opportunity_id' => $opportunity->id, 'job_application_id' => $application->id,
                    'event_type' => 'application_closed_by_candidate', 'agent_source' => 'workspace',
                    'metadata' => ['application_from' => $oldApplicationStatus, 'application_to' => $newApplicationStatus, 'opportunity_from' => $oldJobStatus, 'opportunity_to' => 'rejected', 'reason' => $d['rejection_reason']],
                    'occurred_at' => $rejectedAt,
                ]);
            });

            return redirect()->route('workspace.jobs.rejected')->with('status', 'Opportunity rejected and linked application closed. No submission can be approved.');
        }

        if ($application->status !== 'preparing_application') {
            $transitions->application($application, 'preparing_application');
        }
        $application->forceFill(['status' => 'preparing_application', 'preparation_notes' => $d['note'] ?? $application->preparation_notes, 'approved_by' => null, 'approved_at' => null, 'approved_application_hash' => null, 'approved_document_hashes' => null])->save();

        return back()->with('status', 'Application returned without submission approval.');
    }

    private function owned(Request $r, JobApplication $a): void
    {
        abort_unless($a->user_id === $r->user()->id, 404);
    }
}
