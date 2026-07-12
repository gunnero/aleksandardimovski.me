<header class="site-header" data-nav>
  <div class="container nav-wrap">
    <a class="brand" href="{{ route('home') }}"><span aria-hidden="true">AD</span><strong>Aleksandar Dimovski</strong><span class="sr-only">Home</span></a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav"><span class="sr-only">Toggle navigation</span><span></span><span></span></button>
    <nav id="site-nav" class="nav-links" aria-label="Primary navigation">
      @foreach(['about'=>'About','projects.index'=>'Projects','experience'=>'Experience','resume'=>'Resume','articles.index'=>'Articles','contact'=>'Contact'] as $route => $label)
        <a href="{{ route($route) }}" @class(['active' => request()->routeIs($route) || ($route === 'projects.index' && request()->routeIs('projects.*'))])>{{ $label }}</a>
      @endforeach
      <button class="theme-toggle" type="button" data-theme-toggle aria-label="Use dark theme" aria-pressed="false"><span aria-hidden="true">◐</span></button>
    </nav>
  </div>
</header>
