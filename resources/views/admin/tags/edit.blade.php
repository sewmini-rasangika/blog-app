@extends('layouts.admin')
@section('title', 'Edit Tag')

@section('content')
<h3 class="mb-3">Edit Tag</h3>
<form method="POST" action="{{ route('tags.update', $tag) }}" class="card card-body shadow-sm">
  @csrf
  @method('PUT')
  @include('admin.tags._form', ['button' => 'Update'])
</form>
@endsection