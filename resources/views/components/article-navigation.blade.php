@props(['previous', 'next'])
@if($previous || $next)
<nav class="article-navigation" aria-label="Article pagination">
    @if($previous)<a class="article-nav-link previous" href="{{ route('articles.show', $previous['slug']) }}" aria-label="Previous article: {{ $previous['title'] }}"><span>← Previous article</span><strong>{{ $previous['title'] }}</strong><small>{{ $previous['category'] }}</small></a>@else<span></span>@endif
    @if($next)<a class="article-nav-link next" href="{{ route('articles.show', $next['slug']) }}" aria-label="Next article: {{ $next['title'] }}"><span>Next article →</span><strong>{{ $next['title'] }}</strong><small>{{ $next['category'] }}</small></a>@endif
</nav>
@endif
