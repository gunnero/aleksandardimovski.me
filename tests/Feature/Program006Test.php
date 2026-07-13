<?php

namespace Tests\Feature;

use App\Content\PortfolioContent;
use Tests\TestCase;

class Program006Test extends TestCase
{
    public function test_article_detail_has_compact_semantic_header_and_metadata(): void
    {
        foreach (app(PortfolioContent::class)->articles() as $article) {
            $html = $this->get('/articles/'.$article['slug'])->assertOk()->getContent();
            $this->assertSame(1, preg_match_all('/<h1(?:\s[^>]*)?>/i', $html));
            $this->assertStringContainsString('← Back to Articles', $html);
            $this->assertStringContainsString($article['category'], $html);
            $this->assertStringContainsString('Aleksandar Dimovski', $html);
            $this->assertStringContainsString($article['reading_time'].' min read', $html);
            $this->assertStringContainsString('class="article-content" data-article-body', $html);
            $this->assertStringContainsString('data-reading-progress', $html);
            $this->assertStringContainsString('data-article-toc', $html);
        }

        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('data-reading-progress', $home);
        $this->assertStringNotContainsString('data-article-toc', $home);
    }

    public function test_approved_callouts_render_semantically(): void
    {
        $expectations = [
            'what-building-buildiq-taught-me-about-python-fastapi-and-product-engineering' => 'Key takeaway',
            'modernizing-legacy-php-systems-without-breaking-production' => 'Engineering principle',
            'safe-production-deployments-for-laravel-and-legacy-php' => 'Production note',
        ];

        foreach ($expectations as $slug => $label) {
            $this->get('/articles/'.$slug)
                ->assertOk()
                ->assertSee('class="article-callout"', false)
                ->assertSee('aria-label="'.$label.'"', false);
        }
    }

    public function test_previous_next_states_follow_public_article_order(): void
    {
        $articles = app(PortfolioContent::class)->articles();
        $newest = $this->get('/articles/'.$articles[0]['slug'])->assertOk();
        $newest->assertSee('Previous article')->assertDontSee('Next article →');

        $middle = $this->get('/articles/'.$articles[1]['slug'])->assertOk();
        $middle->assertSee('Previous article')->assertSee('Next article →');

        $oldest = $this->get('/articles/'.$articles[2]['slug'])->assertOk();
        $oldest->assertDontSee('← Previous article')->assertSee('Next article →');
    }

    public function test_related_reading_has_no_current_article_or_duplicates(): void
    {
        foreach (app(PortfolioContent::class)->articles() as $article) {
            $html = $this->get('/articles/'.$article['slug'])->assertOk()->getContent();
            preg_match('/<section class="related-reading".*?<\/section>/s', $html, $related);
            $this->assertNotEmpty($related);
            preg_match_all('/href="[^"]*\/articles\/([^"]+)"/', $related[0], $slugs);
            $this->assertNotContains($article['slug'], $slugs[1]);
            $this->assertSame($slugs[1], array_values(array_unique($slugs[1])));
            $this->assertLessThanOrEqual(3, count($slugs[1]));
            $this->assertStringNotContainsString('engineering-notes-draft', $related[0]);
        }
    }

    public function test_metadata_confidentiality_and_claim_boundaries_remain_valid(): void
    {
        foreach (app(PortfolioContent::class)->articles() as $article) {
            $html = $this->get('/articles/'.$article['slug'])->assertOk()->getContent();
            preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
            $schemas = array_map(fn ($json) => json_decode(trim($json), true, flags: JSON_THROW_ON_ERROR), $matches[1]);
            $articleSchema = collect($schemas)->firstWhere('@type', 'Article');
            $this->assertNotNull($articleSchema);
            $this->assertArrayNotHasKey('sameAs', $articleSchema['author']);
            $this->assertStringContainsString('<link rel="canonical" href="'.url('/articles/'.$article['slug']).'">', $html);

            foreach (['/home/', ' ssh ', 'customer count', 'revenue of', 'years of Python'] as $forbidden) {
                $this->assertStringNotContainsStringIgnoringCase($forbidden, $html);
            }
        }
    }

    public function test_mobile_and_component_styles_are_present(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        foreach (['.article-header', '.article-content', 'overflow-x:auto', '.article-callout', '.article-navigation', '.related-grid', '@media(max-width:700px)', '@media(prefers-reduced-motion:reduce)'] as $rule) {
            $this->assertStringContainsString($rule, $css);
        }
    }
}
