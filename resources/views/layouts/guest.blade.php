<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script>
  (function () {
    var t = null;
    try { t = localStorage.getItem('theme'); } catch (e) {}
    document.documentElement.setAttribute('data-bs-theme', t === 'dark' ? 'dark' : 'light');
    document.documentElement.classList.add('js');
  })();
  </script>

  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  
  <title>{{ config('app.name', 'My Blog') }}</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('css/blog.css') }}?v={{ filemtime(public_path('css/blog.css')) }}" rel="stylesheet">
  <link href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}" rel="stylesheet">
  <link href="{{ asset('css/dark.css') }}?v={{ filemtime(public_path('css/dark.css')) }}" rel="stylesheet">
</head>
<body class="auth-body">
  @include('partials.loader', ['nav' => true])
<div class="top-loader" id="topLoader"></div>
<button type="button" class="theme-float" data-theme-toggle aria-label="Dark / Light" title="Dark / Light">
  <i class="bi bi-moon-stars-fill icon-moon"></i><i class="bi bi-sun-fill icon-sun"></i>
</button>
<div class="auth-wrap">

 
  <aside class="auth-side">
    <span class="orb orb-1"></span>
    <span class="orb orb-2"></span>

    <a class="brand" href="{{ route('home') }}" style="position:relative;z-index:1">
      <span class="brand-mark"><i class="bi bi-pen-fill"></i></span>
      <span>My<span class="brand-accent">Blog</span></span>
    </a>

    <div class="auth-pitch">
      <h2>Your story<br>tell the world ✨</h2>
      <p>Ideas, experiences, knowledge. publish your own articles and find readers who read them.</p>

      <div class="auth-feature">
        <i class="bi bi-pencil-square"></i>
        <div><strong>write</strong><span>Creates post from your own dashboard</span></div>
      </div>
      <div class="auth-feature">
        <i class="bi bi-patch-check-fill"></i>
        <div><strong>Approve </strong><span>Will apear on the site site after admin approvel</span></div>
      </div>
      <div class="auth-feature">
        <i class="bi bi-share-fill"></i>
        <div><strong>Share</strong><span>To readers from Facebook, WhatsApp, X </span></div>
      </div>
    </div>

    <a href="{{ route('home') }}" class="auth-back"><i class="bi bi-arrow-left"></i> Back to site</a>
  </aside>


  <main class="auth-main">
    <div class="auth-card">
      <a class="brand brand-dark d-lg-none mb-4" href="{{ route('home') }}">
        <span class="brand-mark"><i class="bi bi-pen-fill"></i></span>
        <span>My<span class="brand-accent">Blog</span></span>
      </a>

      {{ $slot }}
    </div>
  </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
<script>
  
  document.querySelectorAll('[data-toggle-password]').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.togglePassword);
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
  });
</script>
@stack('scripts')
</body>
</html>
