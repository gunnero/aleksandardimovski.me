<x-workspace.layout :title="$title" :heading="$title">
    <x-slot:description>{{ $description }}</x-slot:description>

    <nav class="status-subnav" aria-label="Opportunity status views">
        @foreach([
            ['inbox','Inbox','workspace.jobs.index'], ['approved','Approved','workspace.jobs.approved'], ['saved','Saved','workspace.jobs.saved'],
            ['research','Research','workspace.jobs.research'], ['rejected','Rejected','workspace.jobs.rejected'], ['all','All','workspace.jobs.all'],
        ] as [$key,$label,$route])
            <a @class(['is-current' => $view === $key]) href="{{ route($route) }}">{{ $label }}</a>
        @endforeach
        <details class="status-subnav__more"><summary>More statuses</summary><div><a href="{{ route('workspace.jobs.duplicates') }}">Duplicates</a><a href="{{ route('workspace.jobs.expired') }}">Expired</a></div></details>
    </nav>

    <form class="filter-bar" method="get">
        <div class="form-field"><label for="q">Search company or role</label><input id="q" name="q" value="{{ request('q') }}" maxlength="200"></div>
        <div class="form-field"><label for="sort">Sort</label><select id="sort" name="sort">
            @foreach(['newest'=>'Newest discovered','oldest'=>'Oldest discovered','highest_fit'=>'Highest fit','lowest_fit'=>'Lowest fit','recently_reviewed'=>'Most recently reviewed','company'=>'Company','role'=>'Role'] as $value=>$label)<option value="{{ $value }}" @selected(request('sort','newest')===$value)>{{ $label }}</option>@endforeach
        </select></div>
        <button class="button button--secondary" type="submit">Apply</button>
        @if(request()->query())<a class="button button--quiet" href="{{ url()->current() }}">Clear</a>@endif
        @if($view === 'all')
            <details class="advanced-filters"><summary>Advanced filters</summary><div class="advanced-filters__grid">
                <div class="form-field"><label for="status">Status</label><select id="status" name="status"><option value="">Every status</option>@foreach(['discovered','needs_review','approved_for_preparation','rejected','saved_for_later','needs_research','duplicate','expired'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ str($status)->replace('_',' ')->title() }}</option>@endforeach</select></div>
                @foreach(['company'=>'Company','role'=>'Role','source'=>'Source','remote_scope'=>'Remote scope'] as $name=>$label)<div class="form-field"><label for="{{ $name }}">{{ $label }}</label><input id="{{ $name }}" name="{{ $name }}" value="{{ request($name) }}"></div>@endforeach
                <div class="form-field"><label for="fit_min">Minimum fit</label><input id="fit_min" name="fit_min" type="number" min="0" max="100" value="{{ request('fit_min') }}"></div>
                <div class="form-field"><label for="fit_max">Maximum fit</label><input id="fit_max" name="fit_max" type="number" min="0" max="100" value="{{ request('fit_max') }}"></div>
                @foreach(['discovered_from'=>'Discovered from','discovered_to'=>'Discovered to','reviewed_from'=>'Reviewed from','reviewed_to'=>'Reviewed to'] as $name=>$label)<div class="form-field"><label for="{{ $name }}">{{ $label }}</label><input id="{{ $name }}" name="{{ $name }}" type="date" value="{{ request($name) }}"></div>@endforeach
            </div></details>
        @endif
    </form>

    <p class="result-count"><strong>{{ $jobs->total() }}</strong> {{ str('opportunity')->plural($jobs->total()) }}</p>
    <div class="inbox-list">
        @forelse($jobs as $job)
            @include('workspace.jobs.partials.card', ['job' => $job, 'view' => $view])
        @empty
            @if($view === 'inbox')<section class="empty-state empty-state--large"><h2>Inbox clear</h2><p>No opportunities are currently waiting for review.</p></section>
            @else<section class="empty-state empty-state--large"><h2>No matching opportunities</h2><p>Try another search or return to the Job Inbox.</p><a class="button button--secondary" href="{{ route('workspace.jobs.index') }}">Open Job Inbox</a></section>@endif
        @endforelse
    </div>
    <div class="pagination">{{ $jobs->links() }}</div>
</x-workspace.layout>
