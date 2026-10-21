<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('code') · {{ config('app.name', 'My Blog') }}</title>

  <script>
    (function () {
      var t = null;
      try { t = localStorage.getItem('theme'); } catch (e) {}
      document.documentElement.setAttribute('data-bs-theme', t === 'dark' ? 'dark' : 'light');
      document.documentElement.classList.add('js');
    })();
  </script>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('css/blog.css') }}" rel="stylesheet">
  <link href="{{ asset('css/error.css') }}" rel="stylesheet">
  <link href="{{ asset('css/dark.css') }}" rel="stylesheet">
</head>
<body class="err-body">
  <span class="err-orb a"></span>
  <span class="err-orb b"></span>

  <main class="err-wrap">
    <a href="{{ url('/') }}" class="err-brand">
      <span class="brand-mark"><i class="bi bi-pen-fill"></i></span>
      <span>My<span class="brand-accent" style="color:var(--violet)">Blog</span></span>
    </a>

    <div class="err-code">@yield('code')</div>
    <h1>@yield('heading')</h1>
    <p>@yield('message')</p>

    <div class="err-actions">
      <a href="{{ url('/') }}" class="btn btn-primary px-4"><i class="bi bi-house-door-fill me-1"></i> Go home</a>
      <button type="button" class="btn btn-outline-dark px-4" onclick="history.back()"><i class="bi bi-arrow-left me-1"></i> Go back</button>
    </div>

    @yield('extra')
  </main>
</body>
</html>