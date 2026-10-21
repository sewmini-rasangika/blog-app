@extends('layouts.admin')
@section('title', 'My Dashboard')

@section('content')
<div class="welcome mb-4">
  <div>
    <h2>Welcome, {{ auth()->user()->name }} 👋</h2>
    <p>Posts ypu submit will apear on the site once approved by the admin.</p>
  </div>
  <a href="{{ route('posts.create') }}" class="btn btn-light fw-semibold"><i class="bi bi-plus-lg me-1"></i> New Post</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <a href="{{ route('posts.index', ['status' => 'all']) }}" class="stat-card">
      <span class="stat-icon violet"><i class="bi bi-journal-text"></i></span>
      <div><div class="stat-num">{{ $counts->sum() }}</div><div class="stat-label">My posts</div></div>
    </a>
  </div>
  <div class="col-6 col-xl-3">
    <a href="{{ route('posts.index', ['status' => 'pending']) }}" class="stat-card">
      <span class="stat-icon amber"><i class="bi bi-hourglass-split"></i></span>
      <div><div class="stat-num">{{ $counts['pending'] ?? 0 }}</div><div class="stat-label">Pending</div></div>
    </a>
  </div>
  <div class="col-6 col-xl-3">
    <a href="{{ route('posts.index', ['status' => 'approved']) }}" class="stat-card">
      <span class="stat-icon green"><i class="bi bi-patch-check-fill"></i></span>
      <div><div class="stat-num">{{ $counts['approved'] ?? 0 }}</div><div class="stat-label">Published</div></div>
    </a>
  </div>
  <div class="col-6 col-xl-3">
    <a href="{{ route('posts.index', ['status' => 'rejected']) }}" class="stat-card">
      <span class="stat-icon rose"><i class="bi bi-x-octagon-fill"></i></span>
      <div><div class="stat-num">{{ $counts['rejected'] ?? 0 }}</div><div class="stat-label">Rejected</div></div>
    </a>
  </div>
</div>

<div class="card shadow-sm">
  <div class="queue-head">
    <h5>🕒 My recent posts</h5>
    <a href="{{ route('posts.index') }}" class="small fw-semibold">View all →</a>
  </div>

  @forelse($recent as $post)
    @php $badge = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$post->status]; @endphp
    <div class="queue-item">
      <div>
        <a href="{{ route('posts.edit', $post) }}" class="q-title">{{ $post->title }}</a>
        <div class="q-sub">{{ $post->category->name }} · {{ $post->created_at->diffForHumans() }}</div>
      </div>
      <div class="q-actions">
        <span class="badge bg-{{ $badge }}">{{ ucfirst($post->status) }}</span>
      </div>
    </div>
  @empty
    <div class="queue-empty">
      <i class="bi bi-pencil-square"></i>
      No posts. Write the first post!
      <div class="mt-3"><a href="{{ route('posts.create') }}" class="btn btn-primary">+ New Post</a></div>
    </div>
  @endforelse
</div>
@endsection