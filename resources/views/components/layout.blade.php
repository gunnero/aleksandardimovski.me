@props(['title', 'description', 'image' => '/images/social-card.png', 'noindex' => false, 'article' => false])
<!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f8fafc" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#09090b" media="(prefers-color-scheme: dark)">
    <title>{{ $title }} · Aleksandar Dimovski</title>
    <meta name="description" content="{{ $description }}">
    @if($noindex)<meta name="robots" content="noindex, nofollow">@endif
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website"><meta property="og:title" content="{{ $title }} · Aleksandar Dimovski"><meta property="og:description" content="{{ $description }}"><meta property="og:url" content="{{ url()->current() }}"><meta property="og:image" content="{{ url($image) }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <script>document.documentElement.dataset.theme=localStorage.getItem('theme')||((matchMedia('(prefers-color-scheme:dark)').matches)?'dark':'light')</script>
    @vite(['resources/css/app.css', 'resources/css/linkedin.css', 'resources/css/print.css', 'resources/js/app.js'])
    @if($article)@vite('resources/js/article.js')@endif
    @php
        $personSchema = ['@context' => 'https://schema.org', '@type' => 'Person', 'name' => 'Aleksandar Dimovski', 'url' => config('app.url'), 'jobTitle' => ['Senior PHP / Laravel Engineer', 'Backend & Product Engineer'], 'description' => 'Senior PHP / Laravel Engineer, Backend & Product Engineer, and Founder & Lead Software Engineer at Kalveri', 'email' => config('portfolio.email'), 'telephone' => config('portfolio.phone'), 'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Bitola', 'postalCode' => '7000', 'addressCountry' => 'MK'], 'worksFor' => ['@type' => 'Organization', 'name' => 'Kalveri'], 'sameAs' => [config('portfolio.github'), config('portfolio.linkedin_url')]];
        $websiteSchema = ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => 'Aleksandar Dimovski', 'url' => config('app.url')];
        $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    @endphp
    <script type="application/ld+json">{!! json_encode($personSchema, $jsonFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($websiteSchema, $jsonFlags) !!}</script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<x-header />
<main id="main">{{ $slot }}</main>
<x-footer />
</body>
</html>
