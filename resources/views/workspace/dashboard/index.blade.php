<x-workspace.layout title="Dashboard" heading="Job search dashboard">
    <x-slot:description>Review opportunities and track every application without exposing private data publicly.</x-slot:description>
    <section class="metric-grid metric-grid--status" aria-label="Opportunity review totals">
        @foreach([
            ['inbox','New jobs to review','workspace.jobs.index'], ['approved','Approved for preparation','workspace.jobs.approved'],
            ['saved','Saved for later','workspace.jobs.saved'], ['research','Needs research','workspace.jobs.research'],
            ['rejected','Rejected','workspace.jobs.rejected'], ['expired','Expired','workspace.jobs.expired'],
        ] as [$key,$label,$route])
            <a class="metric" href="{{ route($route) }}"><strong>{{ $jobCounts[$key] }}</strong><span>{{ $label }}</span></a>
        @endforeach
    </section>
    <x-workspace.card id="applications" title="Applications and submission history">
        @forelse($recentApplications as $application)
            <a class="history-row" href="{{ route('workspace.applications.show',$application) }}"><span><strong>{{ $application->opportunity->role_title }}</strong><small>{{ $application->opportunity->company_name }}</small></span><x-workspace.status-badge :status="$application->status" /></a>
        @empty
            <div class="empty-state"><h3>No applications yet</h3><p>Approve a reviewed opportunity when you are ready to begin preparation.</p><a class="button button--secondary" href="{{ route('workspace.jobs.index') }}">Open job inbox</a></div>
        @endforelse
    </x-workspace.card>
    <x-workspace.card id="follow-ups" title="Follow-ups and interviews"><div class="empty-state"><h3>No follow-ups due</h3><p>Submitted applications, interviews, assessments, and offers will appear here.</p></div></x-workspace.card>
</x-workspace.layout>
