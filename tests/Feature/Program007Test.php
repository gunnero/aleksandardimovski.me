<?php

namespace Tests\Feature;

use App\Content\PortfolioContent;
use Tests\TestCase;

class Program007Test extends TestCase
{
    public function test_approved_public_projects_have_descriptive_repository_links(): void
    {
        foreach (['buildiq' => 'https://github.com/gunnero/BuildIQ', 'mediahub' => 'https://github.com/gunnero/mediahub', 'kalveri' => 'https://github.com/gunnero/kalveri'] as $slug => $repository) {
            $project = app(PortfolioContent::class)->project($slug);
            $this->assertSame($repository, $project['repository']);
            $this->get('/projects/'.$slug)->assertOk()->assertSee($repository)->assertSee('View repository')->assertSee('noopener noreferrer');
        }
    }

    public function test_private_projects_never_expose_repository_urls(): void
    {
        foreach (['hera-backoffice', 'nema30-backoffice', 'razbudise'] as $slug) {
            $project = app(PortfolioContent::class)->project($slug);
            $this->assertNull($project['repository']);
            $this->get('/projects/'.$slug)->assertOk()->assertSee('Private commercial repository.')->assertDontSee('github.com/gunnero/');
        }
    }

    public function test_public_repository_indicators_and_structured_data_are_bounded(): void
    {
        $this->get('/projects')->assertOk()
            ->assertSee('Public repository')
            ->assertSee('Private case study')
            ->assertDontSee('Tv'.'time')
            ->assertDontSee('onefivefour'.'-ai-platform');
        $this->get('/projects/buildiq')->assertSee('"codeRepository":"https://github.com/gunnero/BuildIQ"', false);
    }

    public function test_resume_contains_clickable_public_evidence_and_local_qr_assets(): void
    {
        $this->get('/resume')->assertOk()
            ->assertSee('Public engineering evidence')
            ->assertSee('https://aleksandardimovski.me/projects/buildiq')
            ->assertSee('https://aleksandardimovski.me/projects/mediahub')
            ->assertSee('https://github.com/gunnero/aleksandardimovski.me')
            ->assertSee('https://github.com/gunnero/kalveri')
            ->assertSee('https://www.linkedin.com/in/dimovskialeksandar/')
            ->assertDontSee('onefivefour'.'-ai-platform');

        $this->assertFileExists(public_path('images/qr/buildiq-case-study.png'));
        $this->assertFileExists(public_path('images/qr/mediahub-case-study.png'));
    }
}
