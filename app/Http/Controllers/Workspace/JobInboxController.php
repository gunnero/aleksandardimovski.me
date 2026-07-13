<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\AgentActivity;
use App\Models\JobApplication;
use App\Models\JobOpportunity;
use App\Models\OpportunityReviewHistory;
use App\Services\Workspace\StateTransitions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class JobInboxController extends Controller
{
    public function index(Request $r)
    {
        return $this->listing($r, 'inbox');
    }

    public function approved(Request $r)
    {
        return $this->listing($r, 'approved');
    }

    public function rejected(Request $r)
    {
        return $this->listing($r, 'rejected');
    }

    public function saved(Request $r)
    {
        return $this->listing($r, 'saved');
    }

    public function research(Request $r)
    {
        return $this->listing($r, 'research');
    }

    public function duplicates(Request $r)
    {
        return $this->listing($r, 'duplicates');
    }

    public function expired(Request $r)
    {
        return $this->listing($r, 'expired');
    }

    public function all(Request $r)
    {
        return $this->listing($r, 'all');
    }

    public function show(Request $r, JobOpportunity $job)
    {
        abort_unless($job->user_id === $r->user()->id, 404);

        $job->load(['application.questions', 'reviewHistory']);

        return view('workspace.jobs.show', compact('job'));
    }

    public function review(Request $r, JobOpportunity $job, StateTransitions $transitions)
    {
        abort_unless($job->user_id === $r->user()->id, 404);
        $data = $r->validate(['action' => 'required|in:approved_for_preparation,rejected,saved_for_later,needs_research,duplicate,expired', 'note' => 'nullable|string|max:2000', 'rejection_reason' => 'nullable|required_if:action,rejected|in:compensation,location,weak_fit,company_concern,duplicate,expired,unrelated_stack,other']);
        DB::transaction(function () use ($r, $job, $data, $transitions): void {
            $oldStatus = $job->review_status;
            $transitions->job($job, $data['action']);
            $reviewedAt = now();
            $job->forceFill(['review_status' => $data['action'], 'review_notes' => $data['note'] ?? null, 'rejection_reason' => $data['rejection_reason'] ?? null, 'reviewed_by' => $r->user()->id, 'reviewed_at' => $reviewedAt])->save();
            if ($data['action'] === 'approved_for_preparation' && ! $job->application()->exists()) {
                $application = new JobApplication(['status' => 'preparing_application', 'final_application_url' => $job->original_url]);
                $application->user_id = $r->user()->id;
                $application->job_opportunity_id = $job->id;
                $application->save();
            }
            OpportunityReviewHistory::create(['job_opportunity_id' => $job->id, 'reviewed_by' => $r->user()->id, 'old_status' => $oldStatus, 'new_status' => $data['action'], 'review_note' => $data['note'] ?? null, 'action' => 'decision', 'reviewed_at' => $reviewedAt]);
            AgentActivity::create(['user_id' => $r->user()->id, 'job_opportunity_id' => $job->id, 'event_type' => $data['action'] === 'approved_for_preparation' ? 'approval_granted' : 'job_reviewed', 'agent_source' => 'workspace', 'metadata' => ['from' => $oldStatus, 'to' => $data['action']], 'occurred_at' => $reviewedAt]);
        });

        return redirect()->route($this->routeForStatus($data['action']))->with('status', 'Review decision saved. The opportunity moved to '.str($data['action'])->replace('_', ' ')->title().'.');
    }

    public function restore(Request $r, JobOpportunity $job, StateTransitions $transitions)
    {
        abort_unless($job->user_id === $r->user()->id, 404);
        if (! in_array($job->review_status, ['rejected', 'saved_for_later', 'needs_research', 'duplicate', 'expired'], true)) {
            throw ValidationException::withMessages(['status' => 'This opportunity cannot be restored to the inbox.']);
        }
        if ($job->review_status === 'expired' && (! in_array($job->source_status, ['open', 'verified_open'], true) || ! $job->source_verified_at || ($job->reviewed_at && $job->source_verified_at->lte($job->reviewed_at)) || ($job->application_deadline && $job->application_deadline->isPast()))) {
            throw ValidationException::withMessages(['status' => 'Expired opportunities require newer open-source verification and a current deadline before restoration.']);
        }
        DB::transaction(function () use ($r, $job, $transitions): void {
            $oldStatus = $job->review_status;
            $transitions->job($job, 'needs_review');
            $reviewedAt = now();
            $job->forceFill(['review_status' => 'needs_review', 'reviewed_by' => $r->user()->id, 'reviewed_at' => $reviewedAt])->save();
            OpportunityReviewHistory::create(['job_opportunity_id' => $job->id, 'reviewed_by' => $r->user()->id, 'old_status' => $oldStatus, 'new_status' => 'needs_review', 'review_note' => $job->review_notes, 'action' => 'restore', 'reviewed_at' => $reviewedAt]);
            AgentActivity::create(['user_id' => $r->user()->id, 'job_opportunity_id' => $job->id, 'event_type' => 'job_restored', 'agent_source' => 'workspace', 'metadata' => ['from' => $oldStatus, 'to' => 'needs_review'], 'occurred_at' => $reviewedAt]);
        });

        return redirect()->route('workspace.jobs.index')->with('status', 'Opportunity restored to the Job Inbox. Prior review history was preserved.');
    }

    private function listing(Request $r, string $view)
    {
        $definitions = [
            'inbox' => ['Job Inbox', 'Only opportunities waiting for your decision appear here.', 'pendingReview'],
            'approved' => ['Approved', 'Opportunities approved for application preparation.', 'approved'],
            'rejected' => ['Rejected', 'Opportunities declined during human review.', 'rejected'],
            'saved' => ['Saved for later', 'Promising opportunities intentionally paused for later review.', 'saved'],
            'research' => ['Needs research', 'Opportunities waiting for additional evidence before a decision.', 'needsResearch'],
            'duplicates' => ['Duplicates', 'Opportunities marked as duplicates of existing research or applications.', 'duplicates'],
            'expired' => ['Expired', 'Closed or expired opportunities retained for review history.', 'expired'],
            'all' => ['All opportunities', 'Search, filter, and sort the complete private opportunity history.', null],
        ];
        [$title, $description, $scope] = $definitions[$view];
        $query = JobOpportunity::query()->where('user_id', $r->user()->id)->with(['application.questions']);
        if ($scope) {
            $query->{$scope}();
        }
        $this->applyFilters($query, $r, $view === 'all');

        return view('workspace.jobs.index', ['jobs' => $query->paginate(15)->withQueryString(), 'view' => $view, 'title' => $title, 'description' => $description]);
    }

    private function applyFilters(Builder $query, Request $r, bool $all): void
    {
        $data = $r->validate([
            'q' => 'nullable|string|max:200', 'sort' => ['nullable', Rule::in(['newest', 'oldest', 'highest_fit', 'lowest_fit', 'recently_reviewed', 'company', 'role'])],
            'status' => ['nullable', Rule::in(['discovered', 'needs_review', 'approved_for_preparation', 'rejected', 'saved_for_later', 'needs_research', 'duplicate', 'expired'])],
            'company' => 'nullable|string|max:200', 'role' => 'nullable|string|max:200', 'source' => 'nullable|string|max:100', 'remote_scope' => 'nullable|string|max:200',
            'fit_min' => 'nullable|integer|between:0,100', 'fit_max' => 'nullable|integer|between:0,100|gte:fit_min', 'discovered_from' => 'nullable|date', 'discovered_to' => 'nullable|date|after_or_equal:discovered_from', 'reviewed_from' => 'nullable|date', 'reviewed_to' => 'nullable|date|after_or_equal:reviewed_from',
        ]);
        if (! empty($data['q'])) {
            $query->where(fn (Builder $q) => $q->where('company_name', 'like', '%'.$data['q'].'%')->orWhere('role_title', 'like', '%'.$data['q'].'%'));
        }
        if ($all) {
            foreach (['status' => 'review_status', 'company' => 'company_name', 'role' => 'role_title', 'source' => 'source', 'remote_scope' => 'remote_scope'] as $input => $column) {
                if (! empty($data[$input])) {
                    $query->where($column, $input === 'status' ? '=' : 'like', $input === 'status' ? $data[$input] : '%'.$data[$input].'%');
                }
            }
        }
        if ($all && isset($data['fit_min'])) {
            $query->where('fit_score', '>=', $data['fit_min']);
        }
        if ($all && isset($data['fit_max'])) {
            $query->where('fit_score', '<=', $data['fit_max']);
        }
        foreach (['discovered_from' => ['discovered_at', '>='], 'discovered_to' => ['discovered_at', '<='], 'reviewed_from' => ['reviewed_at', '>='], 'reviewed_to' => ['reviewed_at', '<=']] as $input => [$column, $operator]) {
            if ($all && ! empty($data[$input])) {
                $query->whereDate($column, $operator, $data[$input]);
            }
        }
        match ($data['sort'] ?? 'newest') {
            'oldest' => $query->oldest('discovered_at'), 'highest_fit' => $query->orderByDesc('fit_score'), 'lowest_fit' => $query->orderBy('fit_score'), 'recently_reviewed' => $query->orderByDesc('reviewed_at'), 'company' => $query->orderBy('company_name'), 'role' => $query->orderBy('role_title'), default => $query->latest('discovered_at'),
        };
    }

    private function routeForStatus(string $status): string
    {
        return match ($status) {
            'approved_for_preparation' => 'workspace.jobs.approved', 'rejected' => 'workspace.jobs.rejected', 'saved_for_later' => 'workspace.jobs.saved', 'needs_research' => 'workspace.jobs.research', 'duplicate' => 'workspace.jobs.duplicates', 'expired' => 'workspace.jobs.expired', default => 'workspace.jobs.index'
        };
    }
}
