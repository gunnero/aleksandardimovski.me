<x-workspace.layout title="Sign in" heading="Private job workspace" eyebrow="Authorized access only" :guest="true">
    <x-slot:description>Confidential job-search and application information for one authorized owner.</x-slot:description>
    <section class="login-card">
        <form method="post" action="{{ route('workspace.login.store') }}">@csrf
            <div class="form-field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" autofocus @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>@error('email')<p id="email-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="current-password"></div>
            <label class="check-field"><input type="checkbox" name="remember" value="1"><span>Remember this trusted device</span></label>
            <button class="button button--primary button--full" type="submit">Sign in securely</button>
        </form>
    </section>
</x-workspace.layout>
