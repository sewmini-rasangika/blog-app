@extends('layouts.admin')
@section('title', 'New Category')

@section('content')
<h3 class="mb-3">New Category</h3>
<form method="POST" action="{{ route('categories.store') }}" class="card card-body shadow-sm">
  @csrf
  @include('admin.categories._form', ['button' => 'Create'])
</form>
@endsection