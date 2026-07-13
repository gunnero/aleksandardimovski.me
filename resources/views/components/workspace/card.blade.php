@props(['title' => null])
<section {{ $attributes->class('workspace-card') }}>@if($title)<h2>{{ $title }}</h2>@endif{{ $slot }}</section>
