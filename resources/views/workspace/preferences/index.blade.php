<x-workspace.layout title="Preferences" heading="Job preferences">
    <x-slot:description>Confirmed rules used to explain discovery exclusions and score adjustments.</x-slot:description>
    <section class="metric-grid metric-grid--status" aria-label="Preference rule totals">
        @foreach([['hard','Active hard exclusions'],['penalties','Active penalties'],['confirmation','Rules requiring confirmation'],['disabled','Disabled rules']] as [$key,$label])
            <div class="metric"><strong>{{ $ruleCounts[$key] }}</strong><span>{{ $label }}</span></div>
        @endforeach
    </section>
    <x-workspace.callout tone="info" title="Rules remain under your control"><p>Hard exclusions apply only after explicit confirmation. Disable, expire, edit, or delete any reusable rule here.</p></x-workspace.callout>
    @forelse($rules as $rule)
        <x-workspace.card :title="str($rule->rule_type)->replace('_',' ')->title()">
            <div class="badge-group"><x-workspace.status-badge :status="$rule->severity" /><x-workspace.status-badge :status="$rule->scope" /><x-workspace.status-badge :status="$rule->confirmed_at ? ($rule->active ? 'active' : 'disabled') : 'confirmation_required'" /></div>
            <form method="post" action="{{ route('workspace.preferences.update',$rule) }}">@csrf @method('patch')
                <div class="form-grid"><div class="form-field"><label for="severity-{{ $rule->id }}">Severity</label><select id="severity-{{ $rule->id }}" name="severity"><option value="hard_exclusion" @selected($rule->severity==='hard_exclusion')>Hard exclusion</option><option value="strong_penalty" @selected($rule->severity==='strong_penalty')>Strong penalty</option><option value="soft_penalty" @selected($rule->severity==='soft_penalty')>Soft penalty</option><option value="informational" @selected($rule->severity==='informational')>Informational</option></select></div><div class="form-field"><label for="scope-{{ $rule->id }}">Scope</label><select id="scope-{{ $rule->id }}" name="scope">@foreach(['all_jobs'=>'All jobs','role_family'=>'Similar roles','company'=>'This company','source'=>'This source','country'=>'Country','technology'=>'Technology'] as $value=>$label)<option value="{{ $value }}" @selected($rule->scope===$value)>{{ $label }}</option>@endforeach</select></div></div>
                <div class="form-field"><label for="reason-{{ $rule->id }}">Rule explanation</label><textarea id="reason-{{ $rule->id }}" name="reason" required>{{ $rule->reason }}</textarea></div>
                <div class="form-field"><label for="expires-{{ $rule->id }}">Expires <span>Optional</span></label><input id="expires-{{ $rule->id }}" type="datetime-local" name="expires_at" value="{{ $rule->expires_at?->format('Y-m-d\TH:i') }}"></div>
                <label class="check-field"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" @checked($rule->active)><span>Rule active</span></label>
                <div class="action-bar"><button class="button button--primary" type="submit">Save rule</button><a class="button button--secondary" href="{{ route('workspace.preferences.affected',$rule) }}">View affected jobs ({{ $rule->evaluations_count }})</a></div>
            </form>
            <form method="post" action="{{ route('workspace.preferences.destroy',$rule) }}" data-confirm="Delete this preference rule?">@csrf @method('delete')<button class="button button--danger" type="submit">Delete rule</button></form>
        </x-workspace.card>
    @empty <div class="empty-state"><h2>No preference rules yet</h2><p>Reusable rules explicitly created from rejection decisions will appear here.</p></div> @endforelse
</x-workspace.layout>
