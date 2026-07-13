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
        foreach (['November 2018 – January 2025', '2026 – Present', 'Senior PHP Developer / Server Administrator', 'aleksandar.dimovski@me.com', '+389 75 458 790', 'Bitola 7000, North Macedonia'] as $fact) {
            $resume->assertSee($fact);
        }
        $resume->assertDontSee('LinkedIn');
        $resume->assertDontSee('Date of birth');
        $resume->assertDontSee('Nationality');
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
