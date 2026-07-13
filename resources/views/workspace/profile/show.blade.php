@php($fields = ['full_name','professional_email','phone','location','timezone','portfolio_url','github_url','linkedin_url','primary_title','secondary_title','professional_summary','salary_minimum','salary_target','salary_currency','salary_period','notice_period','availability','remote_preference','employment_preference','work_authorization_notes'])
<x-workspace.layout title="Candidate profile" heading="Candidate profile">
    <x-slot:description>Verified portfolio facts and clearly marked information that still requires your confirmation.</x-slot:description>
    <x-workspace.callout tone="warning" title="Facts stay within verified boundaries"><p>Legal, authorization, availability, salary, visa, reference, notice-period, and start-date answers are never inferred.</p></x-workspace.callout>
    <form class="profile-form" method="post" action="{{ route('workspace.profile.update') }}">@csrf @method('put')
        <x-workspace.card title="Profile facts"><div class="profile-grid">
        @foreach($fields as $field)
            @php($state = old("field_states_json.$field", $profile?->field_states_json[$field] ?? 'user_confirmation_required'))
            <div @class(['form-field','form-field--wide' => in_array($field,['professional_summary','work_authorization_notes'])])>
                <div class="label-row"><label for="{{ $field }}">{{ str($field)->replace('_',' ')->title() }}</label><x-workspace.status-badge :status="$state" /></div>
                @if(in_array($field,['professional_summary','work_authorization_notes']))<textarea id="{{ $field }}" name="{{ $field }}" rows="5" @error($field) aria-invalid="true" aria-describedby="error-{{ $field }}" @enderror>{{ old($field,$profile?->$field) }}</textarea>@else<input id="{{ $field }}" name="{{ $field }}" value="{{ old($field,$profile?->$field) }}" @error($field) aria-invalid="true" aria-describedby="error-{{ $field }}" @enderror>@endif
                @error($field)<p class="field-error" id="error-{{ $field }}">{{ $message }}</p>@enderror
                <label class="visually-hidden" for="state-{{ $field }}">Verification state for {{ str($field)->replace('_',' ') }}</label><select id="state-{{ $field }}" name="field_states_json[{{ $field }}]"><option value="user_confirmation_required" @selected($state==='user_confirmation_required')>User confirmation required</option><option value="verified" @selected($state==='verified')>Verified</option><option value="intentionally_omitted" @selected($state==='intentionally_omitted')>Intentionally omitted</option></select>
            </div>
        @endforeach
        </div></x-workspace.card>
        <div class="sticky-action-bar"><p>Changes are encrypted where the field is sensitive.</p><button class="button button--primary" type="submit">Save private profile</button></div>
    </form>
</x-workspace.layout>
