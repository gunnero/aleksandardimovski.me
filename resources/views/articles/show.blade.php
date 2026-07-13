<x-layout :title="$article['title']" :description="$article['description']" :article="true">
@php
    $articleSchema = ['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $article['title'], 'description' => $article['description'], 'datePublished' => $article['published_at'], 'dateModified' => $article['modified_at'], 'author' => ['@type' => 'Person', 'name' => 'Aleksandar Dimovski', 'url' => route('home')], 'mainEntityOfPage' => url()->current(), 'keywords' => implode(', ', $article['keywords'])];
    $rendered = Str::markdown($article['body'], ['html_input' => 'strip', 'allow_unsafe_links' => false]);
    $usedIds = [];
    $toc = [];
    $rendered = preg_replace_callback('/<h2>(.*?)<\/h2>/s', function ($match) use (&$usedIds, &$toc) {
        $label = trim(strip_tags($match[1]));
        $base = Str::slug($label) ?: 'section';
        $id = $base;
        $suffix = 2;
        while (in_array($id, $usedIds, true)) $id = $base.'-'.$suffix++;
        $usedIds[] = $id;
        $toc[] = ['id' => $id, 'label' => $label];
        return '<h2 id="'.$id.'">'.$match[1].'</h2>';
    }, $rendered);
    $calloutTypes = ['Key takeaway', 'Engineering principle', 'Common mistake', 'Security note', 'Production note'];
    $rendered = preg_replace_callback('/<blockquote>\s*<p><strong>('.implode('|', array_map('preg_quote', $calloutTypes)).')<\/strong><\/p>\s*<p>(.*?)<\/p>\s*<\/blockquote>/s', fn ($match) => '<aside class="article-callout" aria-label="'.$match[1].'"><strong>'.$match[1].'</strong><p>'.$match[2].'</p></aside>', $rendered);
@endphp
<script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<div class="reading-progress" aria-hidden="true"><span data-reading-progress></span></div>
<header class="article-header"><div class="container article-axis"><a class="back" href="{{ route('articles.index') }}">← Back to Articles</a><p class="eyebrow">{{ $article['category'] }}</p><h1>{{ $article['title'] }}</h1><p class="article-summary">{{ $article['description'] }}</p><x-article-meta :article="$article" /></div></header>
<div class="container article-layout">
    @if(count($toc) >= 5)<aside class="article-rail"><nav data-article-toc aria-label="On this page"><strong>On this page</strong><ol>@foreach($toc as $heading)<li><a href="#{{ $heading['id'] }}">{{ $heading['label'] }}</a></li>@endforeach</ol></nav></aside>@endif
    <article class="article-content" data-article-body>{!! $rendered !!}</article>
</div>
<footer class="article-end"><div class="container article-axis"><dl><div><dt>Published</dt><dd><time datetime="{{ $article['published_at'] }}">{{ Illuminate\Support\Carbon::parse($article['published_at'])->format('F j, Y') }}</time></dd></div>@if($article['modified_at'] !== $article['published_at'])<div><dt>Updated</dt><dd><time datetime="{{ $article['modified_at'] }}">{{ Illuminate\Support\Carbon::parse($article['modified_at'])->format('F j, Y') }}</time></dd></div>@endif<div><dt>Category</dt><dd>{{ $article['category'] }}</dd></div></dl><div class="article-end-links"><a href="{{ route('articles.index') }}">All Articles</a><a href="{{ route('engineering-principles') }}">Engineering Principles</a></div><x-article-navigation :previous="$previous" :next="$next" /><x-related-articles :articles="$related" /></div></footer>
</x-layout>
