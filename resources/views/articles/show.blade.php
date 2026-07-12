<x-layout :title="$article['title']" :description="$article['description']">
@php($articleSchema = ['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $article['title'], 'description' => $article['description'], 'datePublished' => $article['published_at'], 'author' => ['@type' => 'Person', 'name' => 'Aleksandar Dimovski'], 'mainEntityOfPage' => url()->current()])
<script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<x-page-hero eyebrow="Article" :title="$article['title']" :intro="$article['description']"/>
<article class="section container prose article-body">{!! Str::markdown($article['body'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</article>
</x-layout>
