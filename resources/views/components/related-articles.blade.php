@props(['articles'])
@if($articles->isNotEmpty())
<section class="related-reading" aria-labelledby="related-reading-title">
    <p class="eyebrow">Continue reading</p>
    <h2 id="related-reading-title">Related engineering notes</h2>
    <div class="related-grid">
        @foreach($articles as $related)
        <article><p class="eyebrow">{{ $related['category'] }}</p><h3><a href="{{ route('articles.show', $related['slug']) }}">{{ $related['title'] }}</a></h3><p>{{ $related['description'] }}</p><span>{{ $related['reading_time'] }} min read</span></article>
        @endforeach
    </div>
</section>
@endif
