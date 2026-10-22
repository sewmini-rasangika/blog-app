<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Dashboard') · {{ config('app.name', 'My Blog') }}</title>

  <script>
    (function () {
      var t = null;
      try { t = localStorage.getItem('theme'); } catch (e) {}
      document.documentElement.setAttribute('data-bs-theme', t === 'dark' ? 'dark' : 'light');
      document.documentElement.classList.add('js');
    })();
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('css/blog.css') }}?v={{ filemtime(public_path('css/blog.css')) }}" rel="stylesheet">
  <link href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}" rel="stylesheet">
  @stack('styles')
  <link href="{{ asset('css/dark.css') }}?v={{ filemtime(public_path('css/dark.css')) }}" rel="stylesheet">
</head>
<body class="admin-body">
@include('partials.loader', ['nav' => false])
<div class="top-loader" id="topLoader"></div>

@php
  $me = auth()->user();
  $is = fn (...$r) => request()->routeIs(...$r) ? 'active' : '';
  $pendingCount = $pendingCount ?? 0;
  $pendingComments = $pendingComments ?? 0;
  $meAvatar = $meAvatar ?? null;
@endphp

<div class="sidebar-backdrop" id="sbBackdrop"></div>


<aside class="sidebar" id="sidebar">
  <a class="brand" href="{{ route('home') }}">
    <span class="brand-mark"><i class="bi bi-pen-fill"></i></span>
    <span>My<span class="brand-accent">Blog</span></span>
  </a>


  @can('posts.create')
    <a href="{{ route('posts.create') }}" class="side-cta"><i class="bi bi-plus-lg"></i> New Post</a>
  @endcan

  <nav>
    <div class="side-label">Menu</div>
    <a class="side-link {{ $is('dashboard') }}" href="{{ route('dashboard') }}">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="side-link {{ $is('posts.index', 'posts.create', 'posts.edit') }}" href="{{ route('posts.index') }}">
      <i class="bi bi-file-earmark-text"></i> {{ $me->can('posts.view_all') ? 'Posts' : 'My Posts' }}
      @if($pendingCount > 0)
        <span class="side-badge">{{ $pendingCount }}</span>
      @endif
    </a>

   
    @if($me->isAdmin() || $me->canAny(['categories.manage', 'tags.manage', 'users.manage', 'comments.moderate', 'posts.trash', 'settings.manage']))
      <div class="side-label">Manage</div>
    @endif

    @can('categories.manage')
      <a class="side-link {{ $is('categories.*') }}" href="{{ route('categories.index') }}"><i class="bi bi-folder2-open"></i> Categories</a>
    @endcan

    @can('tags.manage')
      <a class="side-link {{ $is('tags.*') }}" href="{{ route('tags.index') }}"><i class="bi bi-tags"></i> Tags</a>
    @endcan

    @can('comments.moderate')
      <a class="side-link {{ $is('comments.*') }}" href="{{ route('comments.index') }}">
        <i class="bi bi-chat-dots"></i> Comments
        @if($pendingComments > 0)
          <span class="side-badge">{{ $pendingComments }}</span>
        @endif
      </a>
    @endcan

    @can('users.manage')
      <a class="side-link {{ $is('users.*') }}" href="{{ route('users.index') }}"><i class="bi bi-people"></i> Users</a>
    @endcan

   
    @if($me->isAdmin())
      <a class="side-link {{ $is('roles.*') }}" href="{{ route('roles.index') }}"><i class="bi bi-shield-lock"></i> Roles &amp; Permissions</a>
    @endif

    @can('posts.trash')
      <a class="side-link {{ $is('posts.trash') }}" href="{{ route('posts.trash') }}"><i class="bi bi-trash3"></i> Trash</a>
    @endcan

    @can('activity.view')
  <a class="side-link {{ $is('activity.*') }}" href="{{ route('activity.index') }}"><i class="bi bi-clock-history"></i> Activity Log</a>
@endcan

    @can('settings.manage')
      <a class="side-link {{ $is('settings.*') }}" href="{{ route('settings.mail') }}"><i class="bi bi-envelope"></i> Mail settings</a>
    @endcan

    <div class="side-label">Account</div>
    <a class="side-link {{ $is('myprofile.*') }}" href="{{ route('myprofile.edit') }}"><i class="bi bi-person-circle"></i> My Profile</a>
    <a class="side-link {{ $is('profile.*') }}" href="{{ route('profile.edit') }}"><i class="bi bi-gear"></i> Account settings</a>
    <a class="side-link" href="{{ route('home') }}" target="_blank"><i class="bi bi-box-arrow-up-right"></i> View site</a>
    <a class="side-link {{ $is('tokens.*') }}" href="{{ route('tokens.index') }}"><i class="bi bi-key"></i> API Tokens</a>
  </nav>

  <div class="side-user">
    <span class="avatar-md">
      @if($meAvatar)
        <img src="{{ asset('storage/' . $meAvatar) }}" alt="">
      @else
        {{ mb_strtoupper(mb_substr($me->name, 0, 1)) }}
      @endif
    </span>
    <div>
      <strong>{{ \Illuminate\Support\Str::limit($me->name, 18) }}</strong>
      <small>{{ $me->isAdmin() ? 'Administrator' : ucfirst($me->role) }}</small>
    </div>
  </div>
</aside>


<div class="admin-main">

  <header class="topbar">
    <div class="d-flex align-items-center gap-3">
      <button type="button" class="icon-btn menu-btn" id="menuBtn" aria-label="Menu"><i class="bi bi-list fs-5"></i></button>
      <h1 class="topbar-title">@yield('title', 'Dashboard')</h1>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button type="button" class="icon-btn" data-theme-toggle aria-label="Dark / Light" title="Dark / Light">
        <i class="bi bi-moon-stars-fill icon-moon"></i><i class="bi bi-sun-fill icon-sun"></i>
      </button>
      <a href="{{ route('home') }}" target="_blank" class="icon-btn" title="View site"><i class="bi bi-globe2"></i></a>

              @php
        $unread = $me->unreadNotifications()->count();
        $latest = $me->notifications()->latest()->take(5)->get();
      @endphp
      <div class="dropdown">
        <button type="button" class="icon-btn position-relative" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
          <i class="bi bi-bell-fill"></i>
          @if($unread)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unread > 9 ? '9+' : $unread }}</span>
          @endif
        </button>

        <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 320px;">
          <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <strong>Notifications</strong>
            @if($unread)
              <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button class="btn btn-link btn-sm p-0">Mark all as read</button>
              </form>
            @endif
          </div>

          @forelse($latest as $n)
            <a href="{{ route('notifications.read', $n->id) }}"
               class="dropdown-item d-flex gap-2 py-2 {{ $n->read_at ? '' : 'fw-semibold' }}"
               style="white-space: normal;">
              <i class="bi bi-check-circle-fill text-success mt-1"></i>
              <span>
                {{ $n->data['message'] ?? 'Notification' }}
                <small class="d-block text-muted">{{ $n->created_at->diffForHumans() }}</small>
              </span>
            </a>
          @empty
            <div class="text-center text-muted py-4 small">No notifications yet</div>
          @endforelse
        </div>
      </div>
      <div class="dropdown">
        <button class="user-chip" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="avatar-md">
            @if($meAvatar)
              <img src="{{ asset('storage/' . $meAvatar) }}" alt="">
            @else
              {{ mb_strtoupper(mb_substr($me->name, 0, 1)) }}
            @endif
          </span>
          <span class="d-none d-sm-inline">{{ \Illuminate\Support\Str::limit($me->name, 16) }}</span>
          <i class="bi bi-chevron-down small"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li class="px-3 py-2">
            <div class="fw-semibold">{{ $me->name }}</div>
            <div class="small text-muted">{{ $me->email }}</div>
            <span class="role-pill mt-1 d-inline-block">{{ $me->isAdmin() ? 'Administrator' : ucfirst($me->role) }}</span>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="{{ route('myprofile.edit') }}"><i class="bi bi-person-circle me-2"></i>My Profile</a></li>
          <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear me-2"></i>Account settings</a></li>
          <li>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </header>

  <main class="admin-content">
    @yield('content')
  </main>
</div>

{{-- Toast notifications --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000; margin-top:62px;">
  @php
    $statusMsg = [
      'profile-updated'  => 'updated account details ✨',
      'password-updated' => 'changed password 🔒',
    ][session('status')] ?? null;
  @endphp
  @if($statusMsg)
    <div class="toast app-toast ok" role="alert" data-bs-delay="4500">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-check-circle-fill me-2"></i>{{ $statusMsg }}</div>
        <button type="button" class="btn-close btn-close-white m-auto me-3" data-bs-dismiss="toast"></button>
      </div>
    </div>
  @endif

  @if(session('success'))
    <div class="toast app-toast ok" role="alert" data-bs-delay="4500">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
        <button type="button" class="btn-close btn-close-white m-auto me-3" data-bs-dismiss="toast"></button>
      </div>
    </div>
  @endif

  @if(session('error'))
    <div class="toast app-toast bad" role="alert" data-bs-delay="6000">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
        <button type="button" class="btn-close btn-close-white m-auto me-3" data-bs-dismiss="toast"></button>
      </div>
    </div>
  @endif

  @if($errors->any())
    <div class="toast app-toast bad" role="alert" data-bs-delay="6000">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-exclamation-triangle-fill me-2"></i>Form  has {{ $errors->count() }}errors. test.</div>
        <button type="button" class="btn-close btn-close-white m-auto me-3" data-bs-dismiss="toast"></button>
      </div>
    </div>
  @endif
</div>

<x-delete-modal />

<button type="button" class="to-top" id="toTop" aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
<script>
  // Mobile sidebar
  const body = document.body;
  document.getElementById('menuBtn').addEventListener('click', () => body.classList.toggle('sidebar-open'));
  document.getElementById('sbBackdrop').addEventListener('click', () => body.classList.remove('sidebar-open'));


  document.querySelectorAll('.app-toast').forEach(el => bootstrap.Toast.getOrCreateInstance(el).show());
</script>
@stack('scripts')
</body>
</html>