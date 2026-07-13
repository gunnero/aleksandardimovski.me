@props(['article'])
<div class="article-meta-line" aria-label="Article details">
    <span class="article-author">Aleksandar Dimovski</span>
    <span aria-hidden="true">·</span>
    <time datetime="{{ $article['published_at'] }}">{{ Illuminate\Support\Carbon::parse($article['published_at'])->format('F j, Y') }}</time>
    <span aria-hidden="true">·</span>
    <span>{{ $article['reading_time'] }} min read</span>
</div>
