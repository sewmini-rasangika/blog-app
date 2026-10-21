@extends('layouts.admin')
@section('title', $readonly ? 'View role' : 'Edit role')

@section('content')
<h3 class="mb-1">{{ $role->name }}</h3>
@if($readonly)
  <p class="text-muted"><i class="bi bi-lock-fill"></i> Admin role has all permission. this cannot change.</p>
@endif

<form method="POST" action="{{ route('roles.update', $role) }}">
  @csrf
  @method('PUT')
  @include('admin.roles._form', ['button' => 'Save changes'])
</form>
@endsection