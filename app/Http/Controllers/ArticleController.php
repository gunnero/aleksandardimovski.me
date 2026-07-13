<?php

namespace App\Http\Controllers;

use App\Content\PortfolioContent;

final class ArticleController extends Controller
{
    public function index(PortfolioContent $content)
    {
        return view('articles.index', ['articles' => $content->articles()]);
    }

    public function show(string $article, PortfolioContent $content)
    {
        abort_unless($item = $content->article($article), 404);

        $articles = collect($content->articles());
        $index = $articles->search(fn (array $candidate) => $candidate['slug'] === $article);
        $related = collect($item['related_slugs'] ?? [])
            ->map(fn (string $slug) => $articles->firstWhere('slug', $slug))
            ->filter()
            ->take(3)
            ->values();

        return view('articles.show', [
            'article' => $item,
            'previous' => $articles->get($index + 1),
            'next' => $index > 0 ? $articles->get($index - 1) : null,
            'related' => $related,
        ]);
    }
}
