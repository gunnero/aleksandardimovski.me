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
            <a @class(['is-current' => request()->routeIs('workspace.dashboard')]) href="{{ route('workspace.dashboard') }}">Dashboard</a>
            <a @class(['is-current' => request()->routeIs('workspace.jobs.*')]) href="{{ route('workspace.jobs.index') }}">Job inbox</a>
            <a @class(['is-current' => request()->routeIs('workspace.profile.*')]) href="{{ route('workspace.profile.show') }}">Candidate profile</a>
            <a href="{{ route('workspace.dashboard') }}#applications">Applications</a>
            <a href="{{ route('workspace.dashboard') }}#follow-ups">Follow-ups</a>
        </nav>
        <button class="button button--quiet theme-toggle" type="button" data-theme-toggle aria-pressed="false">Use dark theme</button>
        <form class="workspace-signout" method="post" action="{{ route('workspace.logout') }}">@csrf<button class="button button--quiet" type="submit">Sign out</button></form>
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
