@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
@php $pending = $counts['pending'] ?? 0; @endphp

<div class="welcome mb-4">
  <div>
    <h2>Hi, {{ auth()->user()->name }} 👋</h2>
    <p>
      @if($pending > 0)
        Approve posts <strong>{{ $pending }}</strong>waiting.
      @else
        All are approved. 🎉
      @endif
    </p>
  </div>
  <a href="{{ route('posts.index', ['status' => 'pending']) }}" class="btn btn-light fw-semibold">
    Review queue <i class="bi bi-arrow-right ms-1"></i>
  </a>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3">
    <a href="{{ route('posts.index', ['status' => 'pending']) }}" class="stat-card">
      <span class="stat-icon amber"><i class="bi bi-hourglass-split"></i></span>
      <div><div class="stat-num">{{ $pending }}</div><div class="stat-label">Pending approval</div></div>
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
  <div class="col-6 col-xl-3">
    <a href="{{ route('users.index') }}" class="stat-card">
      <span class="stat-icon blue"><i class="bi bi-people-fill"></i></span>
      <div><div class="stat-num">{{ $users }}</div><div class="stat-label">Users</div></div>
    </a>
  </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
  <a href="{{ route('categories.index') }}" class="mini-stat"><i class="bi bi-folder2-open"></i> Categories <b>{{ $categories }}</b></a>
  <a href="{{ route('tags.index') }}" class="mini-stat"><i class="bi bi-tags"></i> Tags <b>{{ $tags }}</b></a>
  <a href="{{ route('posts.trash') }}" class="mini-stat"><i class="bi bi-trash3"></i> Trash <b>{{ $trashed }}</b></a>
  <a href="{{ route('comments.index') }}" class="mini-stat"><i class="bi bi-chat-dots"></i> Pending comments <b>{{ $commentsPending }}</b></a>
</div>

<div class="card shadow-sm">
  <div class="queue-head">
    <h5>⏳ Waiting for approval</h5>
    <a href="{{ route('posts.index', ['status' => 'pending']) }}" class="small fw-semibold">View all →</a>
  </div>

  @forelse($pendingPosts as $post)
    <div class="queue-item">
      <span class="avatar-sm">{{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}</span>
      <div>
        <a href="{{ route('posts.edit', $post) }}" class="q-title">{{ $post->title }}</a>
        <div class="q-sub">{{ $post->user->name }} · {{ $post->category->name }} · {{ $post->created_at->diffForHumans() }}</div>
      </div>
      <div class="q-actions">
        <form method="POST" action="{{ route('posts.approve', $post) }}" class="d-inline">
          @csrf @method('PUT')
          <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Approve</button>
        </form>
        <form method="POST" action="{{ route('posts.reject', $post) }}" class="d-inline">
          @csrf @method('PUT')
          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> Reject</button>
        </form>
      </div>
    </div>
  @empty
    <div class="queue-empty">
      <i class="bi bi-emoji-smile"></i>
      No posts to approve.
    </div>
  @endforelse
</div>
@endsection