@extends('layouts.admin')
@section('title', 'Posts')

@section('content')
@php
  $seeAll = auth()->user()->can('posts.view_all');
  $canApprove = auth()->user()->can('posts.approve');
  $badge = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
  $tabs = ['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">{{ $seeAll ? 'All Posts' : 'My Posts' }}</h3>
  @can('posts.create')
    <a href="{{ route('posts.create') }}" class="btn btn-primary">+ New Post</a>
  @endcan
</div>

@if($authorFilter)
  <div class="alert alert-secondary py-2">
    Author: <strong>{{ $authorFilter->name }}</strong>
    <a href="{{ route('posts.index', request()->except(['author', 'page'])) }}" class="ms-2">✕ clear</a>
  </div>
@endif

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3">
  @foreach($tabs as $key => $label)
    <li class="nav-item">
      <a class="nav-link {{ $status === $key ? 'active' : '' }}"
         href="{{ route('posts.index', array_merge(request()->except(['page', 'status']), ['status' => $key])) }}">
        {{ $label }}
        <span class="badge bg-{{ $key === 'pending' ? 'warning' : 'secondary' }}">
          {{ $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0) }}
        </span>
      </a>
    </li>
  @endforeach
</ul>


<form method="GET" class="row g-2 mb-3">
  <input type="hidden" name="status" value="{{ $status }}">
  @if($authorFilter)<input type="hidden" name="author" value="{{ $authorFilter->id }}">@endif
  <div class="col-md-5">
    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by title...">
  </div>
  <div class="col-md-4">
    <select name="category" class="form-select">
      <option value="">All categories</option>
      @foreach($categories as $c)
        <option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3">
    <button class="btn btn-dark">Filter</button>
    <a href="{{ route('posts.index', ['status' => $status]) }}" class="btn btn-outline-secondary">Reset</a>
  </div>
</form>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead>
        <tr>
          <th>Image</th><th>Title</th><th>Category</th>
          @if($seeAll)<th>Author</th>@endif
          <th>Status</th><th>Date</th><th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($posts as $post)
          <tr>
            <td>
              @if($post->featured)
                <img src="{{ asset('storage/' . $post->featured) }}" width="60" class="rounded">
              @else <span class="text-muted">-</span> @endif
            </td>
            <td><a href="{{ route('post.show', $post->slug) }}" target="_blank" class="text-decoration-none">{{ $post->title }}</a></td>
            <td><span class="badge bg-info text-dark">{{ $post->category->name }}</span></td>
            @if($seeAll)
              <td><a href="{{ route('posts.index', ['author' => $post->user_id, 'status' => 'all']) }}" class="text-decoration-none">{{ $post->user->name }}</a></td>
            @endif
            <td><span class="badge bg-{{ $badge[$post->status] }}">{{ ucfirst($post->status) }}</span></td>
            <td class="small text-muted">{{ $post->created_at->format('M d, Y') }}</td>
            <td class="text-end text-nowrap">
              @if($canApprove && $post->status !== 'approved')
                <form method="POST" action="{{ route('posts.approve', $post) }}" class="d-inline">
                  @csrf @method('PUT')
                  <button class="btn btn-sm btn-success">Approve</button>
                </form>
              @endif
              @if($canApprove && $post->status !== 'rejected')
                <form method="POST" action="{{ route('posts.reject', $post) }}" class="d-inline">
                  @csrf @method('PUT')
                  <button class="btn btn-sm btn-outline-danger">Reject</button>
                </form>
              @endif
              @can('update', $post)
                <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-warning">Edit</a>
              @endcan
              @can('delete', $post)
                <button type="button" class="btn btn-sm btn-danger"
                  data-bs-toggle="modal" data-bs-target="#deleteModal"
                  data-action="{{ route('posts.destroy', $post) }}"
                  data-name="{{ $post->title }}">Trash</button>
              @endcan
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted py-4">No posts.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">{{ $posts->links('pagination::bootstrap-5') }}</div>
@endsection