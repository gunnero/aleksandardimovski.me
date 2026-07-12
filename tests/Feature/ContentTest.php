<?php

namespace Tests\Feature;

use App\Content\PortfolioContent;
use Tests\TestCase;

class ContentTest extends TestCase
{
    public function test_project_content_loads_and_has_required_fields(): void
    {
        $projects = app(PortfolioContent::class)->projects();
        $this->assertCount(6, $projects);
        foreach ($projects as $project) {
            $this->assertArrayHasKey('summary', $project);
            $this->assertArrayHasKey('security', $project);
            $this->assertArrayHasKey('confidential', $project);
        }
    }

    public function test_drafts_are_not_publicly_loaded(): void
    {
        $articles = app(PortfolioContent::class)->articles();
        $this->assertCount(1, $articles);
        $this->assertSame('published', $articles[0]['status']);
        $this->get('/articles/engineering-notes-draft')->assertNotFound();
    }

    public function test_unapproved_private_fields_are_stripped(): void
    {
        config()->set('portfolio.projects.0.credentials', 'never-render-this');
        config()->set('portfolio.projects.0.production_url', 'https://private.invalid');
        $project = app(PortfolioContent::class)->projects()[0];
        $this->assertArrayNotHasKey('credentials', $project);
        $this->assertArrayNotHasKey('production_url', $project);
        $this->get('/projects/buildiq')->assertDontSee('never-render-this')->assertDontSee('private.invalid');
    }

    public function test_published_markdown_strips_raw_html_and_unsafe_links(): void
    {
        config()->set('portfolio.articles', [[
            'slug' => 'safe-markdown', 'title' => 'Safe Markdown', 'description' => 'Safety test',
            'published_at' => '2026-07-12', 'reading_time' => 1, 'status' => 'published',
            'body' => '<script>alert(1)</script> [unsafe](javascript:alert(1))',
        ]]);

        $this->get('/articles/safe-markdown')
            ->assertOk()
            ->assertDontSee('alert(1)')
            ->assertDontSee('href="javascript:', false);
    }
}
