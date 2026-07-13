<x-workspace.layout title="Affected jobs" heading="Jobs affected by preference rule">
<x-workspace.breadcrumb><a href="{{ route('workspace.preferences.index') }}">Preferences</a><span>/</span><span>Affected jobs</span></x-workspace.breadcrumb>
<x-workspace.card title="Rule"><p>{{ $rule->reason }}</p></x-workspace.card>
<x-workspace.card title="Evaluations">@forelse($rule->evaluations as $evaluation)<a class="history-row" href="{{ route('workspace.jobs.show',$evaluation->opportunity) }}"><span><strong>{{ $evaluation->opportunity->role_title }}</strong><small>{{ $evaluation->explanation }}</small></span><x-workspace.status-badge :status="$evaluation->decision" /></a>@empty<p>No imported jobs are currently linked to this rule.</p>@endforelse</x-workspace.card>
</x-workspace.layout>
