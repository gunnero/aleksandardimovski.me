<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\AgentActivity;
use App\Models\JobApplication;
use App\Models\JobOpportunity;
use App\Services\Workspace\StateTransitions;
use Illuminate\Http\Request;

class JobInboxController extends Controller
{
    public function index(Request $r)
    {
        return view('workspace.jobs.index', ['jobs' => JobOpportunity::where('user_id', $r->user()->id)->latest('discovered_at')->paginate(25)]);
    }

    public function show(Request $r, JobOpportunity $job)
    {
        abort_unless($job->user_id === $r->user()->id, 404);

        return view('workspace.jobs.show', compact('job'));
    }

    public function review(Request $r, JobOpportunity $job, StateTransitions $transitions)
    {
        abort_unless($job->user_id === $r->user()->id, 404);
        $data = $r->validate(['action' => 'required|in:approved_for_preparation,rejected,saved_for_later,needs_research,duplicate,expired', 'note' => 'nullable|string|max:2000', 'rejection_reason' => 'nullable|required_if:action,rejected|in:compensation,location,weak_fit,company_concern,duplicate,expired,unrelated_stack,other']);
        $transitions->job($job, $data['action']);
        $job->forceFill(['review_status' => $data['action'], 'review_notes' => $data['note'] ?? null, 'rejection_reason' => $data['rejection_reason'] ?? null, 'reviewed_by' => $r->user()->id, 'reviewed_at' => now()])->save();
        if ($data['action'] === 'approved_for_preparation' && ! $job->application) {
            $application = new JobApplication(['status' => 'preparing_application', 'final_application_url' => $job->original_url]);
            $application->user_id = $r->user()->id;
            $application->job_opportunity_id = $job->id;
            $application->save();
        } AgentActivity::create(['user_id' => $r->user()->id, 'job_opportunity_id' => $job->id, 'event_type' => $data['action'] === 'approved_for_preparation' ? 'approval_granted' : 'job_reviewed', 'agent_source' => 'workspace', 'metadata' => ['decision' => $data['action']], 'occurred_at' => now()]);

        return back()->with('status', 'Review decision saved.');
    }
}
