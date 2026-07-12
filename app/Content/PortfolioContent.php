<?php

namespace App\Content;

use Illuminate\Support\Arr;

final class PortfolioContent
{
    private const PROJECT_FIELDS = ['slug', 'name', 'eyebrow', 'summary', 'context', 'problem', 'role', 'responsibilities', 'approach', 'technology', 'challenges', 'security', 'outcome', 'lessons', 'repository', 'confidential', 'featured'];

    private const ARTICLE_FIELDS = ['slug', 'title', 'description', 'published_at', 'reading_time', 'status', 'body'];

    public function projects(): array
    {
        return collect(config('portfolio.projects'))->map(function (array $project): array {
            $public = Arr::only($project, self::PROJECT_FIELDS);
            $public['status'] = in_array($project['slug'], ['buildiq', 'mediahub', 'razbudise', 'kalveri'], true) ? 'Active development' : 'Production modernization and support';

            return $public;
        })->all();
    }

    public function project(string $slug): ?array
    {
        return collect($this->projects())->firstWhere('slug', $slug);
    }

    public function articles(bool $includeDrafts = false): array
    {
        return collect(config('portfolio.articles'))
            ->when(! $includeDrafts, fn ($items) => $items->where('status', 'published'))
            ->map(fn (array $article) => Arr::only($article, self::ARTICLE_FIELDS))->all();
    }

    public function article(string $slug): ?array
    {
        return collect($this->articles())->firstWhere('slug', $slug);
    }
}
