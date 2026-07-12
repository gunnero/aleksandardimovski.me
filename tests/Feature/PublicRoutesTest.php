<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    public static function routes(): array
    {
        return array_map(fn ($uri) => [$uri], ['/', '/about', '/projects', '/projects/buildiq', '/experience', '/resume', '/articles', '/contact', '/privacy', '/robots.txt', '/sitemap.xml']);
    }

    #[DataProvider('routes')]
    public function test_public_routes_are_available(string $uri): void
    {
        $this->get($uri)->assertOk();
    }

    public function test_unknown_routes_use_custom_404(): void
    {
        $this->get('/not-a-real-page')->assertNotFound()->assertSee('This route doesn’t lead anywhere.');
    }
}
