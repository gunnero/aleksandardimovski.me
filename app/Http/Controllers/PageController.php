<?php

namespace App\Http\Controllers;

use App\Content\PortfolioContent;
use Illuminate\Http\Response;

final class PageController extends Controller
{
    public function home(PortfolioContent $content)
    {
        return view('pages.home', [
            'projects' => collect($content->projects())->where('featured', true)->take(3),
            'articles' => collect($content->articles())->take(3),
        ]);
    }

    public function sitemap(PortfolioContent $content): Response
    {
        return response()->view('seo.sitemap', ['projects' => $content->projects(), 'articles' => $content->articles()])->header('Content-Type', 'application/xml');
    }
}
