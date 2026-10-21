@extends('layouts.admin')
@section('title', 'Users')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Users</h3>
  <a href="{{ route('users.create') }}" class="btn btn-primary">+ New User</a>
</div>

<form method="GET" class="mb-3 d-flex gap-2">
  <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name or email...">
  <button class="btn btn-dark">Search</button>
</form>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead>
        <tr><th>Avatar</th><th>Name</th><th>Email</th><th>Role</th><th>Posts</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody>
        @foreach($users as $user)
          <tr>
            <td>
              @if($user->profile?->avatar)
                <img src="{{ asset('storage/' . $user->profile->avatar) }}" width="45" height="45" class="rounded-circle object-fit-cover">
              @else
                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                     style="width:45px;height:45px">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
              @endif
            </td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>

            
            <td>
              @if($user->id !== auth()->id() && (! $user->isAdmin() || auth()->user()->isAdmin()))
                <form method="POST" action="{{ route('users.role', $user) }}">
                  @csrf
                  @method('PUT')
                  <select name="role" class="form-select form-select-sm" style="min-width:130px" onchange="this.form.submit()">
                    @foreach($roles as $r)
                      <option value="{{ $r->slug }}" @selected($user->role === $r->slug)>{{ $r->name }}</option>
                    @endforeach
                  </select>
                </form>
              @else
                <span class="role-pill">{{ $roleNames[$user->role] ?? $user->role }}</span>
              @endif
            </td>

            <td>
              <span class="badge bg-secondary">{{ $user->posts_count }}</span>
              @if($user->pending_count)
                <span class="badge bg-warning text-dark">{{ $user->pending_count }} pending</span>
              @endif
            </td>
            <td class="text-end text-nowrap">
              <a href="{{ route('posts.index', ['author' => $user->id, 'status' => 'all']) }}" class="btn btn-sm btn-outline-primary">View posts</a>
              @if($user->id !== auth()->id() && (! $user->isAdmin() || auth()->user()->isAdmin()))
                <button type="button" class="btn btn-sm btn-danger"
                  data-bs-toggle="modal" data-bs-target="#deleteModal"
                  data-action="{{ route('users.destroy', $user) }}"
                  data-name="{{ $user->name }}">Delete</button>
              @else
                <span class="badge bg-success">{{ $user->id === auth()->id() ? 'You' : 'Protected' }}</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div>
@endsection