<x-layout title="Articles" description="Practical engineering notes from real systems covering PHP, Laravel, Python, legacy modernization, security, deployment, and product engineering.">
<x-page-hero eyebrow="Articles" title="Practical engineering notes from real systems." intro="Writing about PHP, Laravel, Python, legacy modernization, security, deployment, and product engineering based on projects I actively build and maintain."/>
<section class="section"><div class="container"><div class="article-card-grid">
@foreach($articles as $article)
<article class="article-card">
    <div><p class="eyebrow">{{ $article['category'] }}</p><h2><a href="{{ route('articles.show', $article['slug']) }}">{{ $article['title'] }}</a></h2><p>{{ $article['description'] }}</p></div>
    <div class="article-meta"><time datetime="{{ $article['published_at'] }}">{{ Illuminate\Support\Carbon::parse($article['published_at'])->format('F j, Y') }}</time><span>{{ $article['reading_time'] }} min read</span></div>
</article>
@endforeach
</div></div></section>
</x-layout>
