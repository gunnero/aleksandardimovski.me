@props(['title', 'heading' => null, 'eyebrow' => 'Private workspace', 'guest' => false])
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>{{ $title }} · Private job workspace</title>
    @vite(['resources/css/workspace.css','resources/js/workspace.js'])
</head>
<body>
<a class="skip-link" href="#workspace-content">Skip to workspace content</a>
@if(! $guest)
<header class="workspace-header">
    <div class="workspace-header__inner">
        <a class="workspace-brand" href="{{ route('workspace.dashboard') }}"><span>Private workspace</span><strong>Job Search</strong></a>
        <nav class="workspace-nav" aria-label="Workspace navigation">
            <div class="workspace-nav__scroll" data-workspace-nav-scroll>
                <a @class(['is-current' => request()->routeIs('workspace.dashboard')]) @if(request()->routeIs('workspace.dashboard')) aria-current="page" @endif href="{{ route('workspace.dashboard') }}">Dashboard</a>
                <a @class(['is-current' => request()->routeIs('workspace.jobs.index') || request()->routeIs('workspace.jobs.show')]) @if(request()->routeIs('workspace.jobs.index') || request()->routeIs('workspace.jobs.show')) aria-current="page" @endif href="{{ route('workspace.jobs.index') }}">Inbox</a>
                <a @class(['is-current' => request()->routeIs('workspace.jobs.approved')]) @if(request()->routeIs('workspace.jobs.approved')) aria-current="page" @endif href="{{ route('workspace.jobs.approved') }}">Approved</a>
                <a @class(['is-current' => request()->routeIs('workspace.jobs.saved')]) @if(request()->routeIs('workspace.jobs.saved')) aria-current="page" @endif href="{{ route('workspace.jobs.saved') }}">Saved</a>
                <a @class(['is-current' => request()->routeIs('workspace.jobs.research')]) @if(request()->routeIs('workspace.jobs.research')) aria-current="page" @endif href="{{ route('workspace.jobs.research') }}">Research</a>
                <a @class(['is-current' => request()->routeIs('workspace.jobs.rejected')]) @if(request()->routeIs('workspace.jobs.rejected')) aria-current="page" @endif href="{{ route('workspace.jobs.rejected') }}">Rejected</a>
                <a @class(['is-current' => request()->routeIs('workspace.jobs.all')]) @if(request()->routeIs('workspace.jobs.all')) aria-current="page" @endif href="{{ route('workspace.jobs.all') }}">All</a>
                <a data-tablet-overflow @class(['is-current' => request()->routeIs('workspace.profile.*')]) @if(request()->routeIs('workspace.profile.*')) aria-current="page" @endif href="{{ route('workspace.profile.show') }}">Candidate profile</a>
                <a data-tablet-overflow @class(['is-current' => request()->routeIs('workspace.applications.*')]) @if(request()->routeIs('workspace.applications.*')) aria-current="page" @endif href="{{ route('workspace.dashboard') }}#applications">Applications</a>
                <a data-tablet-overflow href="{{ route('workspace.dashboard') }}#follow-ups">Follow-ups</a>
            </div>
            <div @class(['workspace-more', 'is-current' => request()->routeIs('workspace.preferences.*') || request()->routeIs('workspace.jobs.duplicates') || request()->routeIs('workspace.jobs.expired')]) data-workspace-more>
                <button class="workspace-more__trigger" type="button" aria-expanded="false" aria-controls="workspace-more-menu" data-workspace-more-trigger>More <span aria-hidden="true">▾</span></button>
                <div class="workspace-more__menu" id="workspace-more-menu" data-workspace-more-menu hidden>
                    <div data-tablet-overflow-slot></div>
                    <a @class(['is-current' => request()->routeIs('workspace.preferences.*')]) @if(request()->routeIs('workspace.preferences.*')) aria-current="page" @endif href="{{ route('workspace.preferences.index') }}">Preferences</a>
                    <a @class(['is-current' => request()->routeIs('workspace.jobs.duplicates')]) @if(request()->routeIs('workspace.jobs.duplicates')) aria-current="page" @endif href="{{ route('workspace.jobs.duplicates') }}">Duplicates</a>
                    <a @class(['is-current' => request()->routeIs('workspace.jobs.expired')]) @if(request()->routeIs('workspace.jobs.expired')) aria-current="page" @endif href="{{ route('workspace.jobs.expired') }}">Expired</a>
                </div>
            </div>
        </nav>
        <div class="workspace-header__controls">
            <button class="button button--quiet theme-toggle" type="button" data-theme-toggle aria-pressed="false">Use dark theme</button>
            <form class="workspace-signout" method="post" action="{{ route('workspace.logout') }}">@csrf<button class="button button--quiet" type="submit">Sign out</button></form>
        </div>
    </div>
</header>
@endif
<main id="workspace-content" class="workspace-container" tabindex="-1">
    <header class="page-heading"><p class="eyebrow">{{ $eyebrow }}</p><h1>{{ $heading ?? $title }}</h1>@isset($description)<div class="page-heading__description">{{ $description }}</div>@endisset</header>
    @if(session('status'))<div class="flash" role="status">{{ session('status') }}</div>@endif
    <x-workspace.validation-summary />
    {{ $slot }}
</main>
@if($guest)<button class="button button--quiet theme-toggle theme-toggle--guest" type="button" data-theme-toggle aria-pressed="false">Use dark theme</button>@endif
</body>
</html>
