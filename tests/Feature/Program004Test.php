<?php

namespace Tests\Feature;

use App\Content\PortfolioContent;
use Tests\TestCase;

class Program004Test extends TestCase
{
    public function test_primary_and_secondary_identity_are_factual(): void
    {
        $home = $this->get('/')->assertOk();
        $home->assertSee('Senior PHP / Laravel Engineer');
        $home->assertSee('Backend &amp; Product Engineer', false);
        $home->assertSee('more than ten years');
        $home->assertDontSee('Python Engineer');
        $home->assertDontSee('AI Engineer');
    }

    public function test_verified_skill_groups_include_current_product_stack(): void
    {
        $resume = $this->get('/resume')->assertOk();
        foreach (['PHP', 'Laravel', 'Python', 'FastAPI', 'React', 'TypeScript', 'PostgreSQL', 'MySQL', 'MariaDB', 'SQL Server', 'Linux', 'GitHub Actions'] as $skill) {
            $resume->assertSee($skill);
        }

        $resume->assertDontSee('years of Python');
        $resume->assertDontSee('Python Engineer');
    }

    public function test_buildiq_uses_the_verified_python_stack_not_laravel(): void
    {
        $project = app(PortfolioContent::class)->project('buildiq');

        $this->assertSame(['Python', 'FastAPI', 'Starlette', 'PostgreSQL', 'React', 'TypeScript', 'Vite', 'Vitest'], $project['technology']);
        $this->assertStringNotContainsStringIgnoringCase('Laravel', implode(' ', [$project['summary'], $project['executive_summary'], ...$project['architecture']]));

        $this->get('/projects/buildiq')
            ->assertOk()
            ->assertSee('Python · FastAPI · Starlette · PostgreSQL · React · TypeScript · Vite · Vitest')
            ->assertSee('127 backend tests')
            ->assertSee('45 frontend tests')
            ->assertDontSee('Laravel API boundary')
            ->assertDontSee('customer count');
    }

    public function test_resume_keeps_verified_chronology_and_omissions(): void
    {
        $resume = $this->get('/resume')->assertOk();
        foreach (['November 2018 - January 2025', 'Senior PHP Developer / Server Administrator', 'aleksandar.dimovski@me.com', '+389 75 458 790', 'Bitola 7000, North Macedonia'] as $fact) {
            $resume->assertSee($fact);
        }
        $resume->assertDontSee('Founder &amp; Lead Software Engineer', false);
        $this->assertStringNotContainsString('Kalveri', json_encode(config('resume'), JSON_THROW_ON_ERROR));
        $resume->assertDontSee('Laravel 8 From Scratch');
        $resume->assertSee('linkedin.com/in/dimovskialeksandar');
        $resume->assertDontSee('Date of birth');
        $resume->assertDontSee('Nationality');
    }

    public function test_overlapping_roles_explain_the_verified_working_arrangement(): void
    {
        $context = 'Some roles overlap because they were full-time, deliverable-based positions with flexible schedules rather than fixed daily working hours.';
        $arrangement = 'Full-time | Flexible, deliverable-based schedule';

        $resume = $this->get('/resume')->assertOk();
        $resume->assertSee($context);
        $this->assertSame(4, substr_count($resume->getContent(), $arrangement));

        $experience = $this->get('/experience')->assertOk();
        $experience->assertSee('Concurrent working arrangements');
        $experience->assertSee($context);
        $this->assertSame(4, substr_count($experience->getContent(), $arrangement));

        $resume->assertDontSee('employers were aware');
        $experience->assertDontSee('employers were aware');
    }

    public function test_public_identity_does_not_present_personal_projects_as_employment(): void
    {
        $about = $this->get('/about')->assertOk();
        $about->assertSee('personal umbrella project');
        $about->assertSee('not a company, employer, or commercial venture');
        $about->assertDontSee('Founder &amp; Lead Software Engineer', false);

        foreach (['buildiq', 'mediahub', 'razbudise', 'kalveri'] as $slug) {
            $project = $this->get('/projects/'.$slug)->assertOk();
            $project->assertDontSee('Founder and Product Engineer');
            $project->assertDontSee('Founder, Backend &amp; Product Engineer', false);
        }

        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('worksFor', $home);
        $this->assertStringNotContainsString('Founder & Lead Software Engineer at Kalveri', $home);
    }

    public function test_final_resume_pdf_exists(): void
    {
        $path = public_path('files/aleksandar-dimovski-resume.pdf');
        $this->assertFileExists($path);

        $pdf = file_get_contents($path);
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('/Count 2', $pdf);
        $this->assertSame(2, substr_count($pdf, '/MediaBox [0 0 594.95996 841.91998]'));
    }
}
