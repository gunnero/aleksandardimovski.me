<?php

namespace Tests\Feature;

use Tests\TestCase;

class Program002Test extends TestCase
{
    public function test_verified_identity_and_chronology_are_public(): void
    {
        $this->get('/resume')->assertOk()->assertSee('aleksandar.dimovski@me.com')->assertSee('+389 75 458 790')->assertSee('Bitola 7000, North Macedonia')->assertSee('Founder &amp; Lead Software Engineer', false)->assertSee('November 2018 – January 2025')->assertSee('github.com/gunnero');
    }

    public function test_no_linkedin_or_unsupported_commercial_claims_exist(): void
    {
        $content = collect(['config', 'resources/views'])->flatMap(fn ($path) => glob(base_path($path.'/**/*.*')) ?: [])->filter(fn ($file) => is_file($file))->map(fn ($file) => file_get_contents($file))->implode("\n");
        $this->assertStringNotContainsStringIgnoringCase('linkedin', $content);
        foreach (['customer count', 'user count', 'revenue generated'] as $claim) {
            $this->assertStringNotContainsStringIgnoringCase($claim, $content);
        }
    }

    public function test_final_pdf_exists_and_downloads(): void
    {
        $this->assertFileExists(public_path('files/aleksandar-dimovski-resume.pdf'));
        $this->get('/resume/download')->assertOk()->assertDownload('aleksandar-dimovski-resume.pdf');
    }

    public function test_article_is_published_and_draft_is_protected(): void
    {
        $html = $this->get('/articles/modernizing-legacy-php-systems-without-breaking-production')->assertOk()->assertSee('Modernizing Legacy PHP Systems')->getContent();
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
        $schemas = array_map(fn ($json) => json_decode(trim($json), true, flags: JSON_THROW_ON_ERROR), $matches[1]);
        $this->assertTrue(collect($schemas)->contains(fn ($schema) => ($schema['@type'] ?? null) === 'Article'));
        $this->get('/articles/engineering-notes-draft')->assertNotFound();
        $this->get('/sitemap.xml')->assertSee('modernizing-legacy-php-systems-without-breaking-production');
    }

    public function test_social_image_and_project_evidence(): void
    {
        [$width, $height] = getimagesize(public_path('images/social-card.png'));
        $this->assertSame([1200, 630], [$width, $height]);
        $this->get('/')->assertSee('images/social-card.png');
        $this->get('/projects/buildiq')->assertSee('Active development');
        $this->get('/projects/hera-backoffice')->assertSee('Production modernization and support')->assertSee('Confidentiality note');
    }
}
