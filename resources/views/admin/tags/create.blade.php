@extends('layouts.admin')
@section('title', 'New Tag')

@section('content')
<h3 class="mb-3">New Tag</h3>
<form method="POST" action="{{ route('tags.store') }}" class="card card-body shadow-sm">
  @csrf
  @include('admin.tags._form', ['button' => 'Create'])
</form>
@endsection