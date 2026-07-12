{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach([route('home'),route('about'),route('projects.index'),route('engineering-principles'),route('release-history'),route('experience'),route('resume'),route('articles.index'),route('contact')] as $url)<url><loc>{{ $url }}</loc></url>@endforeach
@foreach($projects as $project)<url><loc>{{ route('projects.show', $project['slug']) }}</loc></url>@endforeach
@foreach($articles as $article)<url><loc>{{ route('articles.show', $article['slug']) }}</loc></url>@endforeach
</urlset>
