<article class="opportunity-card">
    <header class="opportunity-card__header">
        <div><p class="company-name">{{ $job->company_name }}</p><h2><a href="{{ route('workspace.jobs.show',$job) }}">{{ $job->role_title }}</a></h2></div>
        <div class="badge-group"><x-workspace.status-badge :status="$job->source_status" /><x-workspace.status-badge :status="$job->review_status" /></div>
    </header>
    <div class="opportunity-summary">
        <div class="score"><strong>{{ $job->fit_score ?? '—' }}</strong><span>Fit score</span></div>
        <dl class="data-list data-list--compact">
            <x-workspace.field-row label="Salary">@if($job->salary_min || $job->salary_max){{ $job->salary_min ? number_format((float)$job->salary_min,0) : 'Not stated' }}@if($job->salary_max)–{{ number_format((float)$job->salary_max,0) }}@endif {{ $job->salary_currency }}{{ $job->salary_period ? '/'.$job->salary_period : '' }}@else<x-workspace.empty-value label="Not listed" />@endif</x-workspace.field-row>
            <x-workspace.field-row label="Remote eligibility">{{ $job->location_eligibility ?: ($job->remote_scope ?: 'User confirmation required') }}</x-workspace.field-row>
            <x-workspace.field-row label="Decision date">{{ $job->reviewed_at?->format('M j, Y H:i') ?? 'Awaiting decision' }}</x-workspace.field-row>
            @if($job->review_notes)<x-workspace.field-row label="Review note">{{ $job->review_notes }}</x-workspace.field-row>@endif
            @if($job->review_status === 'rejected')<x-workspace.field-row label="Rejection reason">{{ match($job->rejection_reason) { 'remote_policy_mismatch' => 'Remote policy does not match requirements', 'compensation' => 'Compensation mismatch', 'location' => 'Location or timezone mismatch', 'weak_fit' => 'Role no longer suitable', 'company_concern' => 'Company concern', 'duplicate' => 'Duplicate opportunity', 'expired' => 'Opportunity expired', 'unrelated_stack' => 'Role no longer suitable', 'other' => 'Other', default => 'Not specified' } }}</x-workspace.field-row>@endif
            @if($job->review_status === 'expired')<x-workspace.field-row label="Deadline">{{ $job->application_deadline?->toDateString() ?? 'No deadline listed' }}</x-workspace.field-row><x-workspace.field-row label="Source status"><x-workspace.status-badge :status="$job->source_status" /></x-workspace.field-row>@endif
            @if($job->application)<x-workspace.field-row label="Application"><span><x-workspace.status-badge :status="$job->application->status" /> · {{ $job->application->questions->where('requires_user_confirmation',true)->whereNull('confirmed_at')->count() }} unresolved</span></x-workspace.field-row>@endif
        </dl>
    </div>
    <div class="opportunity-links"><a href="{{ route('workspace.jobs.show',$job) }}">Review opportunity details</a><a href="{{ $job->original_url }}" rel="noreferrer noopener" target="_blank">Open original posting</a>@if($job->application)<a href="{{ route('workspace.applications.show',$job->application) }}">Open application</a>@endif</div>
    @if($view === 'inbox')
        <form class="decision-form" method="post" action="{{ route('workspace.jobs.review',$job) }}">@csrf @method('patch')
            <div class="form-grid"><div class="form-field"><label for="action-{{ $job->id }}">Review decision</label><select id="action-{{ $job->id }}" name="action"><option value="approved_for_preparation">Approve for preparation</option><option value="rejected">Reject</option><option value="saved_for_later">Save for later</option><option value="needs_research">Request more research</option><option value="duplicate">Mark duplicate</option><option value="expired">Mark expired</option></select></div><div class="form-field"><label for="reason-{{ $job->id }}">Rejection reason</label><select id="reason-{{ $job->id }}" name="rejection_reason"><option value="">Not applicable</option>@foreach(['compensation','location','weak_fit','company_concern','duplicate','expired','unrelated_stack','other'] as $reason)<option value="{{ $reason }}">{{ str($reason)->replace('_',' ')->title() }}</option>@endforeach</select></div></div>
            <div class="form-field"><label for="note-{{ $job->id }}">Review note <span>Optional</span></label><textarea id="note-{{ $job->id }}" name="note" rows="3"></textarea></div>
            <x-workspace.action-bar><x-workspace.button type="submit">Save review decision</x-workspace.button></x-workspace.action-bar>
        </form>
    @elseif(in_array($job->review_status,['rejected','saved_for_later','needs_research','duplicate'],true))
        <div class="status-actions"><form method="post" action="{{ route('workspace.jobs.restore',$job) }}" data-confirm="Restore this opportunity to the Job Inbox? Prior review history will be preserved.">@csrf @method('patch')<button class="button button--secondary" type="submit">Restore to inbox</button></form>
        @if(in_array($job->review_status,['saved_for_later','needs_research'],true))<form method="post" action="{{ route('workspace.jobs.review',$job) }}">@csrf @method('patch')<input type="hidden" name="action" value="approved_for_preparation"><button class="button button--primary" type="submit">{{ $job->review_status === 'needs_research' ? 'Approve after research' : 'Approve for preparation' }}</button></form><form method="post" action="{{ route('workspace.jobs.review',$job) }}">@csrf @method('patch')<input type="hidden" name="action" value="rejected"><input type="hidden" name="rejection_reason" value="other"><button class="button button--quiet" type="submit">Reject</button></form>@endif</div>
    @endif
</article>
