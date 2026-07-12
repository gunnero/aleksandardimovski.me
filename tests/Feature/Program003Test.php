<?php

namespace Tests\Feature;

use App\Content\PortfolioContent;
use Tests\TestCase;

class Program003Test extends TestCase
{
    private const REQUIRED_SECTIONS = [
        'Executive Summary',
        'Problem',
        'Role',
        'Responsibilities',
        'Technical Stack',
        'Architecture',
        'Engineering Challenges',
        'Security Considerations',
        'Production Considerations',
        'Lessons Learned',
        'Current Status',
        'Future Roadmap',
    ];

    public function test_every_project_has_the_complete_engineering_case_study(): void
    {
        $projects = app(PortfolioContent::class)->projects();

        $this->assertCount(6, $projects);

        foreach ($projects as $project) {
            $response = $this->get('/projects/'.$project['slug'])->assertOk();

            foreach (self::REQUIRED_SECTIONS as $section) {
                $response->assertSee('>'.$section.'<', false);
            }

            foreach (['executive_summary', 'architecture', 'production', 'roadmap', 'diagram'] as $field) {
                $this->assertNotEmpty($project[$field], $project['slug'].' is missing '.$field);
            }
        }
    }

    public function test_each_architecture_diagram_is_downloadable_mermaid_source(): void
    {
        foreach (app(PortfolioContent::class)->projects() as $project) {
            $response = $this->get('/projects/'.$project['slug'].'/architecture.mmd')
                ->assertOk()
                ->assertHeader('content-type', 'text/plain; charset=UTF-8')
                ->assertHeader('content-disposition', 'attachment; filename="'.$project['slug'].'-architecture.mmd"');

            $this->assertMatchesRegularExpression('/^(flowchart|stateDiagram)/', $response->getContent());
        }
    }

    public function test_engineering_principles_and_release_history_are_public_and_indexed(): void
    {
        $this->get('/engineering-principles')->assertOk()->assertSee('Evidence over claims');
        $this->get('/release-history')->assertOk()->assertSee('Program 003')->assertSee('Engineering evidence and case studies');
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/engineering-principles')
            ->assertSee('/release-history');
    }

    public function test_unapproved_evidence_fields_are_stripped(): void
    {
        config()->set('project_evidence.buildiq.internal_url', 'https://internal.invalid');
        config()->set('project_evidence.buildiq.private_code', 'never-render-this');

        $project = app(PortfolioContent::class)->project('buildiq');

        $this->assertArrayNotHasKey('internal_url', $project);
        $this->assertArrayNotHasKey('private_code', $project);
        $this->get('/projects/buildiq')->assertDontSee('internal.invalid')->assertDontSee('never-render-this');
    }

    public function test_program_003_evidence_avoids_unsupported_commercial_claims(): void
    {
        $content = file_get_contents(config_path('project_evidence.php'));

        foreach (['paying customers', 'monthly revenue', 'active users', 'commercially successful'] as $claim) {
            $this->assertStringNotContainsStringIgnoringCase($claim, $content);
        }
    }
}
