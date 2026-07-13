<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\JobPreferenceRule;
use Illuminate\Http\Request;

class PreferenceRuleController extends Controller
{
    public function index(Request $request)
    {
        $rules = JobPreferenceRule::where('user_id', $request->user()->id)->withCount('evaluations')->latest()->get();

        return view('workspace.preferences.index', compact('rules'));
    }

    public function update(Request $request, JobPreferenceRule $rule)
    {
        $this->owned($request, $rule);
        $data = $request->validate([
            'severity' => 'required|in:hard_exclusion,strong_penalty,soft_penalty,informational',
            'scope' => 'required|in:all_jobs,company,role_family,source,country,technology',
            'reason' => 'required|string|max:2000', 'active' => 'required|boolean', 'expires_at' => 'nullable|date',
        ]);
        $rule->update($data);

        return back()->with('status', 'Preference rule updated.');
    }

    public function destroy(Request $request, JobPreferenceRule $rule)
    {
        $this->owned($request, $rule);
        $rule->delete();

        return back()->with('status', 'Preference rule deleted.');
    }

    public function affected(Request $request, JobPreferenceRule $rule)
    {
        $this->owned($request, $rule);
        $rule->load(['evaluations.opportunity']);

        return view('workspace.preferences.affected', compact('rule'));
    }

    private function owned(Request $request, JobPreferenceRule $rule): void
    {
        abort_unless($rule->user_id === $request->user()->id, 404);
    }
}
