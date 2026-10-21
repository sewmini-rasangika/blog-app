@extends('layouts.admin')
@section('title', 'New permission')

@section('content')
<h3 class="mb-3">New permission</h3>

<div class="alert alert-info">
  <i class="bi bi-info-circle me-1"></i>
  A new permission only <strong>does something</strong> when a page or button checks it, for example
  <code>@@can('reports.view')</code> in a Blade view or <code>->middleware('can:reports.view')</code> on a route.
  Adding it here makes it available to tick for roles.
</div>

<form method="POST" action="{{ route('permissions.store') }}">
  @csrf
  @include('admin.permissions._form', ['button' => 'Add permission'])
</form>
@endsection
