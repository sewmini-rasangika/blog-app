@extends('layouts.admin')
@section('title', 'Tags')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Tags</h3>
  <a href="{{ route('tags.create') }}" class="btn btn-primary">+ New Tag</a>
</div>

<div class="card shadow-sm">
  <table class="table table-hover mb-0 align-middle">
    <thead class="table-light">
      <tr><th>#</th><th>Name</th><th>Posts</th><th class="text-end">Actions</th></tr>
    </thead>
    <tbody>
      @forelse($tags as $tag)
        <tr>
          <td>{{ $tag->id }}</td>
          <td><span class="badge rounded-pill bg-primary">{{ $tag->name }}</span></td>
          <td><span class="badge bg-secondary">{{ $tag->posts_count }}</span></td>
          <td class="text-end">
            <a href="{{ route('tags.edit', $tag) }}" class="btn btn-sm btn-warning">Edit</a>
            <button type="button" class="btn btn-sm btn-danger"
              data-bs-toggle="modal" data-bs-target="#deleteModal"
              data-action="{{ route('tags.destroy', $tag) }}"
              data-name="{{ $tag->name }}">
              Delete
            </button>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-center text-muted py-4">No tags.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">{{ $tags->links('pagination::bootstrap-5') }}</div>
@endsection