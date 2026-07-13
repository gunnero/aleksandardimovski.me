@php($opportunityRejected = $application->opportunity->review_status === 'rejected')
<x-workspace.layout title="Application review" :heading="$application->opportunity->role_title" eyebrow="Application workspace">
    <x-slot:description><strong>{{ $application->opportunity->company_name }}</strong> · <x-workspace.status-badge :status="$application->status" /></x-slot:description>
    <x-workspace.breadcrumb><a href="{{ route('workspace.jobs.index') }}">Job inbox</a><span aria-hidden="true">/</span><a href="{{ route('workspace.jobs.show',$application->opportunity) }}">Opportunity</a><span aria-hidden="true">/</span><span aria-current="page">Application review</span></x-workspace.breadcrumb>

    @if(in_array($application->status,['preparing_application','needs_user_input']))
        <x-workspace.callout tone="info" title="Application preparation"><p>This package is still being prepared. Review every answer and resolve user-input requests before moving it to final review.</p></x-workspace.callout>
    @endif
    @if(in_array($application->status,['withdrawn','closed']))
        <x-workspace.callout tone="danger" :title="$opportunityRejected ? 'Application closed by candidate' : 'Application already closed'"><p>This application is no longer eligible for submission.</p>@if($opportunityRejected)<p><strong>Reason:</strong> {{ match($application->rejection_reason) { 'remote_policy_mismatch' => 'Remote policy does not match requirements', 'compensation' => 'Compensation mismatch', 'location' => 'Location or timezone mismatch', 'weak_fit' => 'Role no longer suitable', 'company_concern' => 'Company concern', 'unrelated_stack' => 'Role no longer suitable', 'other' => 'Other', default => 'Not recorded' } }}</p>@if($application->rejection_note)<p>{{ $application->rejection_note }}</p>@endif @endif</x-workspace.callout>
    @endif
    @unless(in_array($application->status,['withdrawn','closed']))
        <x-workspace.callout tone="warning" title="Final approval is exact and revocable" class="approval-warning"><p>Approving authorizes the exact displayed application package for submission. Any later change invalidates approval.</p></x-workspace.callout>
    @endunless

    <div class="review-grid">
        <div>
            <x-workspace.card title="Posting and planned action">
                <dl class="data-list"><x-workspace.field-row label="Company">{{ $application->opportunity->company_name }}</x-workspace.field-row><x-workspace.field-row label="Role">{{ $application->opportunity->role_title }}</x-workspace.field-row><x-workspace.field-row label="Posting status"><x-workspace.status-badge :status="$application->opportunity->source_status" /></x-workspace.field-row><x-workspace.field-row label="Verified posting URL">@if($application->final_application_url)<a href="{{ $application->final_application_url }}" rel="noreferrer noopener" target="_blank">Open application page</a>@else<x-workspace.empty-value label="Valid URL required" />@endif</x-workspace.field-row></dl>
                <p class="planned-action"><strong>Planned submission action</strong><span>Submit the displayed answers and selected documents once at the verified application URL. Approval does not itself submit.</span></p>
            </x-workspace.card>

            <x-workspace.card title="Application documents">
                <div class="document-list">
                    @foreach(['Canonical CV' => $application->selected_canonical_cv, 'Tailored CV' => $application->tailored_cv_path, 'Cover letter' => $application->cover_letter_path] as $label => $path)
                        <div class="document-row"><span><strong>{{ $label }}</strong>@if($path)<small>{{ basename($path) }}</small>@else<small>No document selected</small>@endif</span>@if($path)<span class="integrity"><span aria-hidden="true">✓</span> Included in integrity check</span>@else<x-workspace.empty-value label="No document selected" />@endif</div>
                    @endforeach
                </div>
                <p class="integrity-summary">@if($application->approved_document_hashes)<strong>Approved document integrity:</strong> {{ count($application->approved_document_hashes) }} stored {{ str('document')->plural(count($application->approved_document_hashes)) }} hashed and protected.@elseNo document hashes recorded because this package has not been approved yet.@endif</p>
            </x-workspace.card>

            <x-workspace.card title="Application answers">
                @if(filled($application->application_answers))<dl class="answer-list">@foreach($application->application_answers as $question => $answer)<x-workspace.field-row :label="str($question)->replace('_',' ')->title()">{{ filled($answer) ? $answer : 'User confirmation required' }}</x-workspace.field-row>@endforeach</dl>@else<div class="empty-state"><h3>No answers prepared</h3><p>Prepared form answers will appear here before final review.</p></div>@endif
                <dl class="data-list"><x-workspace.field-row label="Salary">{{ $application->salary_answer ?: 'User confirmation required' }}</x-workspace.field-row><x-workspace.field-row label="Notice period">{{ $application->notice_period_answer ?: 'User confirmation required' }}</x-workspace.field-row><x-workspace.field-row label="Work authorization">{{ $application->work_authorization_answer ?: 'User confirmation required' }}</x-workspace.field-row></dl>
            </x-workspace.card>

            <x-workspace.card title="Legal, sensitive, and unresolved questions">
                @forelse($application->questions as $question)<article @class(['question-row','question-row--unresolved' => $question->requires_user_confirmation && ! $question->confirmed_at])><div><strong>{{ $question->question }}</strong><p>{{ $question->answer ?: 'User confirmation required' }}</p></div><x-workspace.status-badge :status="$question->confirmed_at ? 'confirmed' : 'user_confirmation_required'" /></article>@empty<div class="empty-state"><h3>No application questions recorded</h3><p>Questions requiring legal or sensitive confirmation will be clearly flagged here.</p></div>@endforelse
            </x-workspace.card>

            <x-workspace.card title="Account and email verification">
                @forelse($application->accountTasks as $task)<div class="account-row"><span><strong>{{ $task->provider ?: 'Application account' }}</strong><small>{{ $task->account_email ?: 'No account email stored' }}</small></span><x-workspace.status-badge :status="$task->verification_status" /></div>@empty<div class="empty-state"><h3>No account required</h3><p>No account-creation or email-verification task is currently recorded.</p></div>@endforelse
            </x-workspace.card>
        </div>

        <aside class="review-actions">
            @if($blockedReasons)
                <x-workspace.callout id="approval-blockers" tone="danger" title="Approval is blocked"><ul>@foreach($blockedReasons as $reason)<li>{{ $reason }}</li>@endforeach</ul></x-workspace.callout>
            @endif
            @if($application->status === 'ready_for_final_review')
                <section class="workspace-card approval-panel" aria-labelledby="approval-heading"><h2 id="approval-heading">Approve exact package</h2><form method="post" action="{{ route('workspace.applications.approve',$application) }}">@csrf
                    <label class="check-field"><input type="checkbox" name="approval_confirmation" value="1" required @disabled($blockedReasons) @if($blockedReasons) aria-describedby="approval-blockers" @endif><span>I confirm that I reviewed the exact answers, documents, sensitive questions, and planned submission action shown here.</span></label>
                    <div class="form-field"><label for="approval_note">Approval note <span>Optional</span></label><textarea id="approval_note" name="approval_note" rows="3"></textarea></div>
                    <button class="button button--primary button--full" type="submit" @disabled($blockedReasons) @if($blockedReasons) aria-describedby="approval-blockers" @endif>{{ $blockedReasons ? 'Resolve blockers before approval' : 'Approve exact package for submission' }}</button>
                </form></section>
            @endif
            @if(in_array($application->status,['preparing_application','needs_user_input','ready_for_final_review','submission_failed']))
                <section class="workspace-card"><h2>Return to preparation</h2><form method="post" action="{{ route('workspace.applications.decision',$application) }}">@csrf<div class="form-field"><label for="decision-note">Review note <span>Optional</span></label><textarea id="decision-note" name="note" rows="3"></textarea></div><div class="action-stack"><button class="button button--secondary button--full" type="submit" name="action" value="request_changes">Request changes</button><button class="button button--secondary button--full" type="submit" name="action" value="return_to_preparation">Return to preparation</button></div></form></section>
            @endif
            @if($application->opportunity->review_status === 'approved_for_preparation')
                <section class="workspace-card"><h2>Reject opportunity</h2><p>Rejecting withdraws this application and permanently blocks submission approval. Prepared documents remain private and are not deleted.</p><form method="post" action="{{ route('workspace.applications.decision',$application) }}">@csrf<input type="hidden" name="action" value="reject"><div class="form-field"><label for="rejection-reason">Rejection reason</label><select id="rejection-reason" name="rejection_reason" required><option value="">Select a reason</option><option value="remote_policy_mismatch">Remote policy does not match requirements</option><option value="compensation">Compensation mismatch</option><option value="location">Location or timezone mismatch</option><option value="weak_fit">Role no longer suitable</option><option value="company_concern">Company concern</option><option value="other">Other</option></select></div><div class="form-field"><label for="rejection-note">Rejection note <span>Optional</span></label><textarea id="rejection-note" name="note" rows="4" placeholder="Remote work is limited to 30 days per year, which does not match my fully remote requirement."></textarea></div><button class="button button--danger button--full" type="submit">Reject and close application</button></form></section>
            @endif
        </aside>
    </div>
</x-workspace.layout>
