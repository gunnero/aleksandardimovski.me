<x-layout title="Experience" description="Verified experience of Aleksandar Dimovski across PHP, Laravel, product engineering, technical leadership, and infrastructure.">
<x-page-hero eyebrow="Experience" title="From hands-on IT operations to senior product engineering." intro="A verified chronology spanning more than a decade of PHP and web software, business systems, infrastructure ownership, and international remote collaboration."/>
<section class="section"><div class="container timeline">
@foreach(config('resume.experience') as $role)
<article><p class="eyebrow">{{ $role['period'] }}</p><h2>{{ $role['title'] }}</h2><h3>{{ $role['company'] }}</h3><p>{{ $role['summary'] }}</p>@if($role['highlights'])<ul>@foreach($role['highlights'] as $item)<li>{{ $item }}</li>@endforeach</ul>@endif</article>
@endforeach
<article><p class="eyebrow">September 2009 – June 2010</p><h2>Education and Training</h2><h3>IT Academy Alexandria</h3><p>Web Design and Internet Technologies training with early practical work in HTML, CSS, visual design, Adobe tools, and web production. Placed under education because it was primarily a training program rather than conventional long-term employment.</p></article>
</div></section>
</x-layout>
