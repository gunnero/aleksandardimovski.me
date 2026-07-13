<x-workspace.layout title="Job review" :heading="$job->role_title" eyebrow="Opportunity review">
    <x-slot:description><strong>{{ $job->company_name }}</strong> · Review source evidence before making a preparation decision.</x-slot:description>
    <x-workspace.breadcrumb><a href="{{ route('workspace.jobs.index') }}">Job inbox</a><span aria-hidden="true">/</span><span aria-current="page">Opportunity</span></x-workspace.breadcrumb>
    <div class="page-grid">
        <div>
            <x-workspace.card title="Opportunity overview"><x-workspace.status-badge :status="$job->review_status" /><dl class="data-list"><x-workspace.field-row label="Fit score">{{ $job->fit_score !== null ? $job->fit_score.'/100' : 'Not scored' }}</x-workspace.field-row><x-workspace.field-row label="Location">{{ $job->location_eligibility ?: ($job->remote_scope ?: 'User confirmation required') }}</x-workspace.field-row><x-workspace.field-row label="Source"><span>{{ $job->source }}</span> <x-workspace.status-badge :status="$job->source_status" /></x-workspace.field-row><x-workspace.field-row label="Deadline">{{ $job->application_deadline?->toDateString() ?? 'No deadline listed' }}</x-workspace.field-row></dl></x-workspace.card>
            <x-workspace.card title="Company summary"><p>{{ $job->company_summary ?: 'No company summary prepared.' }}</p></x-workspace.card>
            <x-workspace.card title="Role description"><div class="prose">{{ $job->job_description ?: 'No role description prepared.' }}</div></x-workspace.card>
        </div>
        <aside><x-workspace.card title="Source verification"><p>The original posting opens outside this private workspace.</p><a class="button button--secondary button--full" href="{{ $job->original_url }}" rel="noopener noreferrer" target="_blank">Open original posting</a></x-workspace.card></aside>
    </div>
</x-workspace.layout>
