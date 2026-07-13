<?php

namespace Tests\Feature;

use App\Content\PortfolioContent;
use Tests\TestCase;

class Program005Test extends TestCase
{
    public function test_articles_index_lists_three_published_articles_and_hides_drafts(): void
    {
        $articles = app(PortfolioContent::class)->articles();
        $this->assertCount(3, $articles);

        $index = $this->get('/articles')->assertOk()->assertSee('Practical engineering notes from real systems.');
        foreach ($articles as $article) {
            $index->assertSee($article['title'])->assertSee($article['category'])->assertSee($article['reading_time'].' min read');
            $this->assertNotEmpty($article['published_at']);
            $this->assertNotEmpty($article['reading_time']);
        }

        $index->assertDontSee('Engineering notes draft');
        $this->get('/articles/engineering-notes-draft')->assertNotFound();
    }

    public function test_each_article_has_unique_metadata_and_valid_article_schema(): void
    {
        $titles = [];
        $descriptions = [];

        foreach (app(PortfolioContent::class)->articles() as $article) {
            $response = $this->get('/articles/'.$article['slug'])->assertOk();
            $html = $response->getContent();
            preg_match('/<title>(.*?)<\/title>/', $html, $title);
            preg_match('/<meta name="description" content="([^"]+)/', $html, $description);
            preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
            $schemas = array_map(fn ($json) => json_decode(trim($json), true, flags: JSON_THROW_ON_ERROR), $matches[1]);
            $schema = collect($schemas)->firstWhere('@type', 'Article');

            $this->assertNotNull($schema);
            $this->assertSame($article['published_at'], $schema['datePublished']);
            $this->assertSame($article['modified_at'], $schema['dateModified']);
            $this->assertStringContainsString('<link rel="canonical"', $html);
            $this->assertStringContainsString('property="og:title"', $html);
            $titles[] = $title[1];
            $descriptions[] = $description[1];
        }

        $this->assertCount(3, array_unique($titles));
        $this->assertCount(3, array_unique($descriptions));
    }

    public function test_sitemap_and_internal_article_links_are_valid(): void
    {
        $sitemap = $this->get('/sitemap.xml')->assertOk();
        foreach (app(PortfolioContent::class)->articles() as $article) {
            $sitemap->assertSee('/articles/'.$article['slug']);

            $html = $this->get('/articles/'.$article['slug'])->getContent();
            preg_match_all('/href="(\/[^"]+)"/', $html, $links);
            $internal = array_filter(array_unique($links[1]), fn ($link) => preg_match('#^/(articles|projects|engineering-principles|resume)(/|$)#', $link));
            foreach ($internal as $link) {
                $this->get($link)->assertSuccessful();
            }
        }
    }

    public function test_article_claims_respect_fact_and_confidentiality_boundaries(): void
    {
        $articles = app(PortfolioContent::class)->articles();
        $all = implode("\n", array_column($articles, 'body'));
        foreach (['LinkedIn', 'date of birth', 'nationality', '/home/', 'ssh ', 'customer count', 'user count', 'revenue of'] as $forbidden) {
            $this->assertStringNotContainsStringIgnoringCase($forbidden, $all);
        }

        $buildiq = collect($articles)->firstWhere('slug', 'what-building-buildiq-taught-me-about-python-fastapi-and-product-engineering')['body'];
        foreach (['BuildIQ is built with Laravel', 'BuildIQ uses Laravel', 'BuildIQ Laravel stack', 'Laravel-based BuildIQ'] as $unsupported) {
            $this->assertStringNotContainsStringIgnoringCase($unsupported, $buildiq);
        }
        $this->assertStringNotContainsStringIgnoringCase('years of Python', $buildiq);
        $this->assertStringNotContainsStringIgnoringCase('Python Engineer', $buildiq);

        $legacy = collect($articles)->firstWhere('slug', 'modernizing-legacy-php-systems-without-breaking-production')['body'];
        $this->assertStringContainsString('Hera Backoffice', $legacy);
        $this->assertStringContainsString('Nema30 Backoffice', $legacy);
        $this->assertStringContainsString('distinct', $legacy);
        $this->assertStringNotContainsString('Thoughtful writing takes a little longer', $all);
    }

    public function test_homepage_shows_latest_writing_without_replacing_projects(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Selected work')
            ->assertSee('Latest writing')
            ->assertSee('What Building BuildIQ Taught Me About Python, FastAPI, and Product Engineering');
    }
}
