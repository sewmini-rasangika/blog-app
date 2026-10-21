@extends('layouts.admin')
@section('title', 'Edit Category')

@section('content')
<h3 class="mb-3">Edit Category</h3>
<form method="POST" action="{{ route('categories.update', $category) }}" class="card card-body shadow-sm">
  @csrf
  @method('PUT')
  @include('admin.categories._form', ['button' => 'Update'])
</form>
@endsection