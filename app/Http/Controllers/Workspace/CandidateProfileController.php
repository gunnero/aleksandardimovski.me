<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\CandidateProfile;
use Illuminate\Http\Request;

class CandidateProfileController extends Controller
{
    public function show(Request $r)
    {
        return view('workspace.profile.show', ['profile' => CandidateProfile::where('user_id', $r->user()->id)->first()]);
    }

    public function update(Request $r)
    {
        $d = $r->validate([
            'full_name' => 'required|string|max:200', 'professional_email' => 'nullable|email:rfc|max:254', 'phone' => ['nullable', 'regex:/^[+0-9 ()-]{7,30}$/'],
            'location' => 'nullable|string|max:200', 'timezone' => 'nullable|timezone', 'portfolio_url' => 'nullable|url:http,https', 'github_url' => 'nullable|url:http,https',
            'linkedin_url' => 'nullable|url:http,https', 'primary_title' => 'nullable|string|max:200', 'secondary_title' => 'nullable|string|max:200',
            'professional_summary' => 'nullable|string|max:5000', 'salary_minimum' => 'nullable|numeric|min:0', 'salary_target' => 'nullable|numeric|min:0|gte:salary_minimum',
            'salary_currency' => 'nullable|string|size:3', 'salary_period' => 'nullable|in:hour,month,year', 'notice_period' => 'nullable|string|max:200',
            'availability' => 'nullable|string|max:200', 'remote_preference' => 'nullable|string|max:200', 'employment_preference' => 'nullable|string|max:200',
            'work_authorization_notes' => 'nullable|string|max:2000', 'field_states_json' => 'required|array',
            'field_states_json.*' => 'in:verified,user_confirmation_required,intentionally_omitted',
        ]);
        $p = CandidateProfile::firstOrNew(['user_id' => $r->user()->id]);
        $p->fill($d);
        $p->user_id = $r->user()->id;
        $p->save();

        return back()->with('status', 'Candidate profile saved privately.');
    }
}
