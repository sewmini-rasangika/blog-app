@extends('layouts.admin')
@section('title', 'Edit Post')

@section('content')
<h3 class="mb-3">Edit Post</h3>
<form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data" class="card card-body shadow-sm">
  @csrf
  @method('PUT')
  @include('admin.posts._form', ['button' => 'Update'])
</form>
@endsection