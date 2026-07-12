<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoTest extends TestCase
{
    public function test_home_has_core_metadata_and_schema(): void
    {
        $this->get('/')->assertOk()->assertSee('<link rel="canonical"', false)->assertSee('property="og:title"', false)->assertSee('application/ld+json', false)->assertSee('Senior PHP / Laravel Engineer');
    }

    public function test_pages_have_unique_descriptions(): void
    {
        $home = $this->get('/')->getContent();
        $about = $this->get('/about')->getContent();
        preg_match('/<meta name="description" content="([^"]+)/', $home, $a);
        preg_match('/<meta name="description" content="([^"]+)/', $about, $b);
        $this->assertNotSame($a[1], $b[1]);
    }

    public function test_error_pages_are_not_indexable(): void
    {
        $this->get('/missing-for-seo-test')->assertNotFound()->assertSee('noindex, nofollow');
    }

    public function test_home_states_core_positioning_immediately(): void
    {
        $response = $this->get('/');
        foreach (['Aleksandar Dimovski', 'Senior PHP / Laravel Engineer', 'Product Engineer', 'more than 10 years', 'enterprise software', 'AI-powered products', 'backend systems'] as $copy) {
            $response->assertSee($copy, false);
        }
    }

    public function test_json_ld_blocks_are_valid_schema(): void
    {
        $html = $this->get('/')->getContent();
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
        $this->assertCount(2, $matches[1]);
        foreach ($matches[1] as $json) {
            $schema = json_decode(trim($json), true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame('https://schema.org', $schema['@context']);
        }
    }
}
