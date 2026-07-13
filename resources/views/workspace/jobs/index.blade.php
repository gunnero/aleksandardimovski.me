<x-workspace.layout title="Job inbox" heading="Job discovery inbox">
    <x-slot:description>Compare fit evidence, risks, and eligibility before approving any application preparation.</x-slot:description>
    <div class="inbox-list">
    @forelse($jobs as $job)
        <article class="opportunity-card">
            <header class="opportunity-card__header">
                <div><p class="company-name">{{ $job->company_name }}</p><h2><a href="{{ route('workspace.jobs.show',$job) }}">{{ $job->role_title }}</a></h2></div>
                <div class="badge-group"><x-workspace.status-badge :status="$job->source_status" /><x-workspace.status-badge :status="$job->review_status" /></div>
            </header>
            <div class="opportunity-summary">
                <div class="score"><strong>{{ $job->fit_score ?? '—' }}</strong><span>Fit score</span></div>
                <dl class="data-list data-list--compact">
                    <x-workspace.field-row label="Salary">@if($job->salary_min || $job->salary_max){{ $job->salary_min ?: '—' }}–{{ $job->salary_max ?: '—' }} {{ $job->salary_currency }}{{ $job->salary_period ? '/'.$job->salary_period : '' }}@else<x-workspace.empty-value label="Not listed" />@endif</x-workspace.field-row>
                    <x-workspace.field-row label="Remote eligibility">{{ $job->location_eligibility ?: ($job->remote_scope ?: 'User confirmation required') }}</x-workspace.field-row>
                    <x-workspace.field-row label="Source">{{ $job->source ?: 'Unknown source' }}</x-workspace.field-row>
                    <x-workspace.field-row label="Deadline">{{ $job->application_deadline?->toDateString() ?? 'No deadline listed' }}</x-workspace.field-row>
                </dl>
            </div>
            <div class="evidence-grid">
                <section><h3>Strongest matches</h3>@if($job->strengths_json)<ul>@foreach($job->strengths_json as $item)<li>{{ $item }}</li>@endforeach</ul>@else<x-workspace.empty-value label="No strengths scored" />@endif</section>
                <section><h3>Important gaps</h3>@if($job->gaps_json)<ul>@foreach($job->gaps_json as $item)<li>{{ $item }}</li>@endforeach</ul>@else<x-workspace.empty-value label="No gaps recorded" />@endif</section>
                <section class="risk-list"><h3>Risks</h3>@if($job->risks_json)<ul>@foreach($job->risks_json as $item)<li>{{ $item }}</li>@endforeach</ul>@else<x-workspace.empty-value label="No risks recorded" />@endif</section>
            </div>
            <div class="opportunity-links"><a href="{{ route('workspace.jobs.show',$job) }}">Review opportunity details</a><a href="{{ $job->original_url }}" rel="noreferrer noopener" target="_blank">Open original posting</a>@if($job->application)<a href="{{ route('workspace.applications.show',$job->application) }}">Open application</a>@endif</div>
            <form class="decision-form" method="post" action="{{ route('workspace.jobs.review',$job) }}">@csrf @method('patch')
                <div class="form-grid"><div class="form-field"><label for="action-{{ $job->id }}">Review decision</label><select id="action-{{ $job->id }}" name="action"><option value="approved_for_preparation">Approve for preparation</option><option value="rejected">Reject</option><option value="saved_for_later">Save for later</option><option value="needs_research">Request more research</option><option value="duplicate">Mark duplicate</option><option value="expired">Mark expired</option></select></div><div class="form-field"><label for="reason-{{ $job->id }}">Rejection reason</label><select id="reason-{{ $job->id }}" name="rejection_reason"><option value="">Not applicable</option>@foreach(['compensation','location','weak_fit','company_concern','duplicate','expired','unrelated_stack','other'] as $reason)<option value="{{ $reason }}">{{ str($reason)->replace('_',' ')->title() }}</option>@endforeach</select></div></div>
                <div class="form-field"><label for="note-{{ $job->id }}">Review note <span>Optional</span></label><textarea id="note-{{ $job->id }}" name="note" rows="3"></textarea></div>
                <x-workspace.action-bar><x-workspace.button type="submit">Save review decision</x-workspace.button></x-workspace.action-bar>
            </form>
        </article>
    @empty
        <section class="empty-state empty-state--large"><h2>No discovered jobs</h2><p>Imported opportunities will appear here for private review.</p></section>
    @endforelse
    </div>
    <div class="pagination">{{ $jobs->links() }}</div>
</x-workspace.layout>
