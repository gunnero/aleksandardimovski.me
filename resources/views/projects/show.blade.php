<x-layout :title="$project['name'].' Engineering Case Study'" :description="$project['executive_summary']">
<section class="page-hero"><div class="container narrow"><a class="back" href="{{ route('projects.index') }}">← All projects</a><p class="eyebrow">{{ $project['eyebrow'] }} · {{ $project['status'] }}</p><h1>{{ $project['name'] }}</h1><p class="lead">{{ $project['executive_summary'] }}</p><div class="tags">@foreach($project['technology'] as $tech)<span>{{ $tech }}</span>@endforeach</div></div></section>
<section class="section"><div class="container case-grid"><aside class="case-nav"><dl><dt>Current status</dt><dd>{{ $project['status'] }}</dd><dt>Role</dt><dd>{{ $project['role'] }}</dd><dt>Evidence</dt><dd>{{ $project['repository'] ? 'Public repository' : 'Sanitized engineering case study' }}</dd></dl><div class="case-actions"><a class="button diagram-download" href="{{ route('projects.diagram', $project['slug']) }}" download>View architecture</a>@if($project['repository'])<a class="button" href="{{ $project['repository'] }}" target="_blank" rel="noopener noreferrer" aria-label="View the {{ $project['name'] }} repository on GitHub (opens in a new tab)">View repository <span aria-hidden="true">↗</span></a>@else<p class="private-evidence"><strong>Private commercial repository.</strong><br>A sanitized engineering case study is available publicly.</p>@endif @if($project['live_url'])<a class="button" href="{{ $project['live_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Visit the {{ $project['name'] }} live site (opens in a new tab)">Visit live site <span aria-hidden="true">↗</span></a>@endif</div></aside><article class="prose case-study">
<h2>Executive Summary</h2><p>{{ $project['executive_summary'] }}</p>
<h2>Problem</h2><p>{{ $project['problem'] }}</p>
<h2>Role</h2><p>{{ $project['role'] }}</p>
<h2>Responsibilities</h2><ul>@foreach($project['responsibilities'] as $item)<li>{{ $item }}</li>@endforeach</ul>
<h2>Technical Stack</h2><p>{{ implode(' · ', $project['technology']) }}</p>
<h2>Architecture</h2>@foreach($project['architecture'] as $paragraph)<p>{{ $paragraph }}</p>@endforeach<div class="architecture-diagram" role="img" aria-label="{{ $project['name'] }} architecture diagram"><pre class="mermaid">{{ $project['diagram'] }}</pre></div>
<h2>Engineering Challenges</h2><p>{{ $project['challenges'] }}</p>
<h2>Security Considerations</h2><p>{{ $project['security'] }}</p>
<h2>Production Considerations</h2><ul>@foreach($project['production'] as $item)<li>{{ $item }}</li>@endforeach</ul>
<h2>Lessons Learned</h2><p>{{ $project['lessons'] }}</p>
<h2>Current Status</h2><p>{{ $project['status'] }}. {{ $project['outcome'] }}</p>
<h2>Future Roadmap</h2><ul>@foreach($project['roadmap'] as $item)<li>{{ $item }}</li>@endforeach</ul>
@if($project['confidential'])<div class="notice"><strong>Confidentiality note</strong><p>This case study intentionally excludes private code, credentials, internal URLs, customer information, production topology, and confidential business processes.</p></div>@endif
</article></div></section>
@if($project['repository'])<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'SoftwareSourceCode', 'name' => $project['name'], 'url' => route('projects.show', $project['slug']), 'codeRepository' => $project['repository']], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>@endif
</x-layout>
