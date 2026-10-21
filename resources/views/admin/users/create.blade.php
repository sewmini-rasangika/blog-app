@extends('layouts.admin')
@section('title', 'New User')

@section('content')
<h3 class="mb-3">New User</h3>
<form method="POST" action="{{ route('users.store') }}" class="card card-body shadow-sm" autocomplete="off">
  @csrf

  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" name="password" autocomplete="new-password" class="form-control @error('password') is-invalid @enderror">
    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="mb-3">
    <label class="form-label">Confirm Password</label>
    <input type="password" name="password_confirmation" autocomplete="new-password" class="form-control">
  </div>

  <div class="mb-3">
    <label class="form-label">Role</label>
    <select name="role" class="form-select @error('role') is-invalid @enderror">
      @foreach($roles as $r)
        <option value="{{ $r->slug }}" @selected(old('role', 'author') === $r->slug)>{{ $r->name }}</option>
      @endforeach
    </select>
    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div>
    <button class="btn btn-success">Create</button>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
  </div>
</form>
@endsection