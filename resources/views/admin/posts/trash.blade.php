@extends('layouts.admin')
@section('title', 'Trash')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>🗑 Trash</h3>
  <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary">← Back to Posts</a>
</div>

<div class="card shadow-sm">
  <table class="table table-hover mb-0 align-middle">
    <thead class="table-light">
      <tr><th>Title</th><th>Category</th><th>Deleted</th><th class="text-end">Actions</th></tr>
    </thead>
    <tbody>
      @forelse($posts as $post)
        <tr>
          <td>{{ $post->title }}</td>
          <td>{{ $post->category->name }}</td>
          <td>{{ $post->deleted_at->diffForHumans() }}</td>
          <td class="text-end">
            <form method="POST" action="{{ route('posts.restore', $post->id) }}" class="d-inline">
              @csrf
              @method('PUT')
              <button class="btn btn-sm btn-success">Restore</button>
            </form>
            <button type="button" class="btn btn-sm btn-danger"
              data-bs-toggle="modal" data-bs-target="#deleteModal"
              data-action="{{ route('posts.force', $post->id) }}"
              data-name="{{ $post->title }} (permanently)">
              Delete Forever
            </button>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-center text-muted py-4">Empty the trash</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">{{ $posts->links('pagination::bootstrap-5') }}</div>
@endsection