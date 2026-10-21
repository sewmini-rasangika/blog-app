@extends('layouts.admin')
@section('title', 'Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Categories</h3>
  <a href="{{ route('categories.create') }}" class="btn btn-primary">+ New Category</a>
</div>

<div class="card shadow-sm">
  <table class="table table-hover mb-0 align-middle">
    <thead class="table-light">
      <tr><th>#</th><th>Name</th><th>Posts</th><th class="text-end">Actions</th></tr>
    </thead>
    <tbody>
      @forelse($categories as $category)
        <tr>
          <td>{{ $category->id }}</td>
          <td>{{ $category->name }}</td>
          <td><span class="badge bg-secondary">{{ $category->posts_count }}</span></td>
          <td class="text-end">
            <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-warning">Edit</a>
            <button type="button" class="btn btn-sm btn-danger"
              data-bs-toggle="modal" data-bs-target="#deleteModal"
              data-action="{{ route('categories.destroy', $category) }}"
              data-name="{{ $category->name }}">
              Delete
            </button>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-center text-muted py-4">No categories.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">{{ $categories->links('pagination::bootstrap-5') }}</div>
@endsection