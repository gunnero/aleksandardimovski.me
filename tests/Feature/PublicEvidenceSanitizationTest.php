<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class PublicEvidenceSanitizationTest extends TestCase
{
    public function test_owned_public_sources_exclude_private_topology_patterns(): void
    {
        $files = collect(['README.md', 'CHANGELOG.md', 'SECURITY.md'])
            ->map(fn (string $path): string => base_path($path))
            ->merge($this->filesIn(['app', 'config', 'docs', 'resources/content', 'resources/views', 'routes', 'tests', '.github']))
            ->reject(fn (string $path): bool => in_array(basename($path), [
                'PublicEvidenceSanitizationTest.php',
                'Program005Test.php',
                'Program006Test.php',
                'github-professionalization-001-audit.md',
                'github-profile-readme-draft.md',
                'github-public-evidence-policy.md',
            ], true));

        $patterns = [
            'private server label' => '/\\bweb'.'\\d{2}\\b/i',
            'home directory' => '#/(?:home|Users)/[^\\s`"\']+#',
            'IP address' => '/(?<![\\d.])(?!127\\.0\\.0\\.1)(?:\\d{1,3}\\.){3}\\d{1,3}(?![\\d.])/',
            'remote shell command' => '/(?:^|[\\s`])s'.'sh\\s+(?:-[A-Za-z]+\\s+)*[^\\s]+@/mi',
            'web-server layout' => '#/(?:etc|var)/(?:apache2|httpd|nginx|www)/#i',
            'private backup location' => '#/(?:home|srv|var)/[^\\s`"\']*/backups?/#i',
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);

            foreach ($patterns as $label => $pattern) {
                $this->assertDoesNotMatchRegularExpression($pattern, $contents, $label.' found in '.str_replace(base_path().'/', '', $file));
            }
        }
    }

    /** @return array<int, string> */
    private function filesIn(array $directories): array
    {
        $files = [];

        foreach ($directories as $directory) {
            $root = base_path($directory);

            if (! is_dir($root)) {
                continue;
            }

            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
                if ($file instanceof SplFileInfo && $file->isFile() && in_array($file->getExtension(), ['md', 'php', 'yml', 'yaml'], true)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }
}
