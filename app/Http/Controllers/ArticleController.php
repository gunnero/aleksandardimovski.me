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

        return view('articles.show', ['article' => $item]);
    }
}
