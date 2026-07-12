@props(['project'])
<article class="project-card"><div><p class="eyebrow">{{ $project['eyebrow'] }}</p><h2><a href="{{ route('projects.show', $project['slug']) }}">{{ $project['name'] }}</a></h2><p>{{ $project['summary'] }}</p></div><div class="card-meta"><span>{{ $project['role'] }}</span><span aria-hidden="true">↗</span></div></article>
