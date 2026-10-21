<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <script>
  (function () {
    var t = null;
    try { t = localStorage.getItem('theme'); } catch (e) {}
    document.documentElement.setAttribute('data-bs-theme', t === 'dark' ? 'dark' : 'light');
    document.documentElement.classList.add('js');
  })();
</script>

  <title>@yield('title', 'My Blog')</title>

  @hasSection('seo')
    @yield('seo')
  @else
    @include('partials.seo', ['seoTitle' => trim($__env->yieldContent('title')) ?: config('seo.site_name')])
  @endif

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('css/blog.css') }}?v={{ filemtime(public_path('css/blog.css')) }}" rel="stylesheet">
  <link href="{{ asset('css/dark.css') }}?v={{ filemtime(public_path('css/dark.css')) }}" rel="stylesheet">
</head>
<body>
@include('partials.loader', ['nav' => true])
<div class="top-loader" id="topLoader"></div>

<nav class="navbar navbar-expand-lg navbar-dark site-nav sticky-top">
  <div class="container">
    <a class="brand" href="{{ route('home') }}">
      <span class="brand-mark"><i class="bi bi-pen-fill"></i></span>
      <span>My<span class="brand-accent">Blog</span></span>
    </a>

    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-lg-4 me-auto gap-lg-1 py-3 py-lg-0">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
            <i class="bi bi-house-door-fill me-1"></i>Home
          </a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ request()->routeIs('category.show') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-grid-fill me-1"></i>Categories
          </a>
          <ul class="dropdown-menu">
            @forelse($navCategories as $c)
              <li><a class="dropdown-item" href="{{ route('category.show', $c->slug) }}">{{ $c->name }}</a></li>
            @empty
              <li><span class="dropdown-item text-muted">No Categories</span></li>
            @endforelse
          </ul>
        </li>
      </ul>

      <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2 pb-3 pb-lg-0">
      <button type="button" class="theme-toggle" data-theme-toggle aria-label="Dark / Light" title="Dark / Light">
        <i class="bi bi-moon-stars-fill icon-moon"></i><i class="bi bi-sun-fill icon-sun"></i>
      </button>

        <form class="nav-search" action="{{ route('search') }}" method="GET">
          <input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search posts...">
          <button type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
        </form>

        @auth
          <a href="{{ route('dashboard') }}" class="btn btn-register"><i class="bi bi-speedometer2 me-1"></i>My Dashboard</a>
        @else
          <a href="{{ route('login') }}" class="btn btn-login">Login</a>
          <a href="{{ route('register') }}" class="btn btn-register">Register</a>
        @endauth
      </div>
    </div>
  </div>
</nav>


<main class="container py-4">
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row g-4">
    <div class="col-lg-8">
      @yield('content')
    </div>

   <aside class="col-lg-4">

 
  @guest
    <div class="card widget widget-join">
      <h5>✍️ You write too!</h5>
      <p>Register and publish your own posts. they will be shown on the site after admin approval.</p>
      <a href="{{ route('register') }}" class="btn btn-light fw-semibold">Register</a>
    </div>
  @endguest

 
  <div class="card widget shadow-sm">
    <h6 class="widget-title"><i class="bi bi-fire"></i> Popular Posts</h6>
    @forelse($popularPosts as $p)
      <a href="{{ route('post.show', $p->slug) }}" class="rank-item">
        <span class="rank-no">{{ $loop->iteration }}</span>
        <span class="rank-thumb">
          @if($p->featured)
            <img src="{{ asset('storage/' . $p->featured) }}" alt="" loading="lazy">
          @endif
        </span>
        <span>
          <p class="rank-title">{{ \Illuminate\Support\Str::limit($p->title, 45) }}</p>
          <span class="rank-views"><i class="bi bi-eye"></i> {{ number_format($p->views) }}</span>
        </span>
      </a>
    @empty
      <div class="p-3 text-muted">No posts.</div>
    @endforelse
  </div>


  <div class="card widget shadow-sm">
    <h6 class="widget-title"><i class="bi bi-grid-fill"></i> Categories</h6>
    @forelse($navCategories as $c)
      <a href="{{ route('category.show', $c->slug) }}" class="cat-link">
        <span>{{ $c->name }}</span>
        <span class="cat-count">{{ $c->posts_count }}</span>
      </a>
    @empty
      <div class="p-3 text-muted">No Categories.</div>
    @endforelse
  </div>

 
  <div class="card widget shadow-sm">
    <h6 class="widget-title"><i class="bi bi-tags-fill"></i> Tags</h6>
    <div class="tag-cloud">
      @forelse($sideTags as $t)
        <a href="{{ route('tag.show', $t->slug) }}" class="tag-chip">#{{ $t->name }}</a>
      @empty
        <span class="text-muted">No tags.</span>
      @endforelse
    </div>
  </div>

</aside>
  </div>
</main>


<footer class="site-footer">
  <div class="container py-5">
    <div class="row g-4">
      <div class="col-lg-5">
        <a class="brand" href="{{ route('home') }}">
          <span class="brand-mark"><i class="bi bi-pen-fill"></i></span>
          <span>My<span class="brand-accent">Blog</span></span>
        </a>
        <p class="mt-3 mb-0 pe-lg-5">Stories, ideas, experience and knowledge. From our writers to you</p>
      </div>

      <div class="col-6 col-lg-3">
        <h6>Explore</h6>
        <ul class="list-unstyled footer-links mb-0">
          <li><a href="{{ route('home') }}">Home</a></li>
          @foreach($navCategories->take(5) as $c)
            <li><a href="{{ route('category.show', $c->slug) }}">{{ $c->name }}</a></li>
          @endforeach
        </ul>
      </div>

      <div class="col-6 col-lg-4">
        <h6>Join us</h6>
        <p class="small">Register and write your own posts.</p>
        @auth
          <a href="{{ route('dashboard') }}" class="btn btn-register btn-sm">My Dashboard</a>
        @else
          <a href="{{ route('register') }}" class="btn btn-register btn-sm">Register</a>
          <a href="{{ route('login') }}" class="btn btn-login btn-sm">Login</a>
        @endauth
      </div>
    </div>

    <div class="footer-bottom d-flex flex-wrap justify-content-between gap-2">
      <span>&copy; {{ date('Y') }} My Blog. All rights reserved.</span>
      <span>Built with <i class="bi bi-heart-fill text-danger"></i> using Laravel</span>
    </div>
  </div>
</footer>
<button type="button" class="to-top" id="toTop" aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>

@stack('scripts')
</body>
</html>