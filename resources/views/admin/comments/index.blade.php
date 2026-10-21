@extends('layouts.admin')
@section('title', 'Comments')

@section('content')
@php
  $tabs = ['pending' => 'Pending', 'approved' => 'Approved', 'spam' => 'Spam', 'all' => 'All'];
  $badge = ['pending' => 'warning', 'approved' => 'success', 'spam' => 'danger'];
@endphp

<h3 class="mb-3">Comments</h3>

<ul class="nav nav-tabs">
  @foreach($tabs as $key => $label)
    <li class="nav-item">
      <a class="nav-link {{ $status === $key ? 'active' : '' }}"
         href="{{ route('comments.index', array_merge(request()->except(['page', 'status']), ['status' => $key])) }}">
        {{ $label }}
        <span class="badge bg-{{ $key === 'pending' ? 'warning' : 'secondary' }}">
          {{ $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0) }}
        </span>
      </a>
    </li>
  @endforeach
</ul>

<form method="GET" class="d-flex gap-2 mb-3">
  <input type="hidden" name="status" value="{{ $status }}">
  <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search comment, name or email...">
  <button class="btn btn-dark">Search</button>
</form>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead>
        <tr><th>Commenter</th><th>Comment</th><th>On post</th><th>Status</th><th>Date</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody>
        @forelse($comments as $c)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <span class="avatar-sm">{{ mb_strtoupper(mb_substr($c->name, 0, 1)) }}</span>
                <div>
                  <div class="fw-semibold">
                    {{ $c->name }}
                    @if($c->user_id) <span class="role-pill">Member</span> @endif
                  </div>
                  <div class="small text-muted">{{ $c->email }}</div>
                </div>
              </div>
            </td>
            <td style="min-width:260px;max-width:380px">
              @if($c->parent)
                <div class="small text-muted mb-1"><i class="bi bi-reply-fill"></i> Reply to {{ $c->parent->name }}</div>
              @endif
              {{ \Illuminate\Support\Str::limit($c->body, 180) }}
            </td>
            <td>
              @if($c->post && ! $c->post->trashed())
                <a href="{{ route('post.show', $c->post->slug) }}#comment-{{ $c->id }}" target="_blank" class="text-decoration-none">
                  {{ \Illuminate\Support\Str::limit($c->post->title, 34) }}
                </a>
              @else
                <span class="text-muted">(A post is in the trash)</span>
              @endif
            </td>
            <td><span class="badge bg-{{ $badge[$c->status] }}">{{ ucfirst($c->status) }}</span></td>
            <td class="small text-muted text-nowrap">{{ $c->created_at->format('M d, H:i') }}</td>
            <td class="text-end text-nowrap">
              @if($c->status !== 'approved')
                <form method="POST" action="{{ route('comments.approve', $c) }}" class="d-inline">
                  @csrf @method('PUT')
                  <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Approve</button>
                </form>
              @endif
              @if($c->status !== 'spam')
                <form method="POST" action="{{ route('comments.spam', $c) }}" class="d-inline">
                  @csrf @method('PUT')
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-slash-circle"></i> Spam</button>
                </form>
              @endif
              <button type="button" class="btn btn-sm btn-danger"
                data-bs-toggle="modal" data-bs-target="#deleteModal"
                data-action="{{ route('comments.destroy', $c) }}"
                data-name="{{ $c->name }}your comment"><i class="bi bi-trash3"></i></button>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-5">No Comments 🎉</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">{{ $comments->links('pagination::bootstrap-5') }}</div>
@endsection