<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="robots" content="noindex,nofollow,noarchive"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Private job workspace</title>@vite('resources/css/workspace.css')
</head>
<body><main><section><p class="private">Authorized access only</p><h1>Private job workspace</h1><p>Job-search and application information is confidential.</p>
    <form method="post" action="{{ route('workspace.login.store') }}">@csrf
        <label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" autofocus>
        @error('email')<p role="alert">{{ $message }}</p>@enderror
        <label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="current-password">
        <label><input type="checkbox" name="remember" value="1" style="width:auto"> Remember this device</label>
        <button type="submit">Sign in securely</button>
    </form>
</section></main></body></html>
