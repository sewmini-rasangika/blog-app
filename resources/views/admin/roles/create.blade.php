@extends('layouts.admin')
@section('title', 'New role')

@section('content')
<h3 class="mb-3">New role</h3>
<form method="POST" action="{{ route('roles.store') }}">
  @csrf
  @include('admin.roles._form', ['button' => 'Create role'])
</form>
@endsection