@extends('layouts.admin')
@section('title', 'Edit permission')

@section('content')
<h3 class="mb-3">Edit permission
  @if($permission->is_system) <span class="role-pill ms-1">System</span> @endif
</h3>

<form method="POST" action="{{ route('permissions.update', $permission) }}">
  @csrf
  @method('PUT')
  @include('admin.permissions._form', ['button' => 'Save changes'])
</form>
@endsection
