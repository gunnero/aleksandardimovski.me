<x-layout title="Release History" description="Verified release history for aleksandardimovski.me, including scope, engineering gates, and production state.">
<x-page-hero eyebrow="Release History" title="A public record of reviewed, verifiable releases." intro="Each release separates implemented scope from deployment state and records only evidence that can be supported."/>
<section class="section"><div class="container release-list">@foreach(config('engineering.releases') as $release)<article><div><p class="eyebrow">{{ $release['version'] }} · {{ $release['date'] }}</p><h2>{{ $release['title'] }}</h2></div><p>{{ $release['summary'] }}</p></article>@endforeach</div></section>
</x-layout>
