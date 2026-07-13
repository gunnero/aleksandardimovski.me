<?php

namespace App\Content;

use Illuminate\Support\Arr;

final class PortfolioContent
{
    private const PROJECT_FIELDS = ['slug', 'name', 'eyebrow', 'summary', 'context', 'problem', 'role', 'responsibilities', 'approach', 'technology', 'challenges', 'security', 'outcome', 'lessons', 'repository', 'confidential', 'featured'];

    private const PUBLIC_PROJECT_LINKS = [
        'buildiq' => ['repository' => 'https://github.com/gunnero/BuildIQ', 'live_url' => null, 'confidential' => false],
        'mediahub' => ['repository' => 'https://github.com/gunnero/mediahub', 'live_url' => null, 'confidential' => false],
        'kalveri' => ['repository' => 'https://github.com/gunnero/kalveri', 'live_url' => 'https://kalveri.com', 'confidential' => false],
    ];

    private const PROJECT_EVIDENCE_FIELDS = ['executive_summary', 'architecture', 'production', 'roadmap', 'diagram'];

    private const ARTICLE_FIELDS = ['slug', 'title', 'description', 'published_at', 'modified_at', 'reading_time', 'category', 'keywords', 'related_slugs', 'status', 'body'];

    public function projects(): array
    {
        return collect(config('portfolio.projects'))->map(function (array $project): array {
            $public = Arr::only($project, self::PROJECT_FIELDS);
            $public = array_merge($public, self::PUBLIC_PROJECT_LINKS[$project['slug']] ?? ['live_url' => null]);
            $public['status'] = in_array($project['slug'], ['buildiq', 'mediahub', 'razbudise', 'kalveri'], true) ? 'Active development' : 'Production modernization and support';
            $evidence = Arr::only(config('project_evidence.'.$project['slug'], []), self::PROJECT_EVIDENCE_FIELDS);
            $public = array_merge($public, $evidence);

            return $public;
        })->all();
    }

    public function project(string $slug): ?array
    {
        return collect($this->projects())->firstWhere('slug', $slug);
    }

    public function articles(bool $includeDrafts = false): array
    {
        return collect(config('articles'))
            ->when(! $includeDrafts, fn ($items) => $items->where('status', 'published'))
            ->map(fn (array $article) => Arr::only($article, self::ARTICLE_FIELDS))->all();
    }

    public function article(string $slug): ?array
    {
        return collect($this->articles())->firstWhere('slug', $slug);
    }
}
