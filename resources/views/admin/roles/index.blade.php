@extends('layouts.admin')
@section('title', 'Roles & Permissions')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <h3 class="mb-0">Roles &amp; Permissions</h3>
    <div class="text-muted">Control what each role is allowed to do.</div>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('permissions.create') }}" class="btn btn-outline-primary"><i class="bi bi-key me-1"></i> New permission</a>
    <a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New role</a>
  </div>
</div>


<div class="card shadow-sm mb-4">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead>
        <tr><th>Role</th><th>Users</th><th>Permissions</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody>
        @foreach($roles as $role)
          <tr>
            <td>
              <div class="fw-semibold">
                {{ $role->name }}
                @if($role->is_system) <span class="role-pill ms-1">System</span> @endif
              </div>
              <div class="small text-muted">{{ $role->description }}</div>
            </td>
            <td>{{ $counts[$role->slug] ?? 0 }}</td>
            <td>{{ $role->slug === 'admin' ? 'All' : count($role->permissions ?? []) }}</td>
            <td class="text-end text-nowrap">
              @if($role->slug === 'admin')
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
              @else
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                @unless($role->is_system)
                  <button type="button" class="btn btn-sm btn-danger" title="Delete"
                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                    data-action="{{ route('roles.destroy', $role) }}"
                    data-name="the {{ $role->name }} role"><i class="bi bi-trash3"></i></button>
                @endunless
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>


<div class="card shadow-sm">
  <div class="queue-head">
    <h5>Permission matrix</h5>
    <a href="{{ route('permissions.create') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg me-1"></i> Add permission</a>
  </div>
  <div class="table-responsive">
    <table class="table mb-0 align-middle text-center">
      <thead>
        <tr>
          <th class="text-start">Permission</th>
          @foreach($roles as $r) <th>{{ $r->name }}</th> @endforeach
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($groups as $group => $perms)
          <tr><td colspan="{{ $roles->count() + 2 }}" class="text-start fw-semibold bg-body-tertiary">{{ $group }}</td></tr>
          @foreach($perms as $key => $label)
            @php $perm = $permissions[$key] ?? null; @endphp
            <tr>
              <td class="text-start">
                {{ $label }}
                @if(\App\Support\Permissions::isSensitive($key)) <span class="badge bg-danger ms-1">Sensitive</span> @endif
                @if($perm && ! $perm->is_system) <span class="role-pill ms-1">Custom</span> @endif
                <div class="small text-muted"><code>{{ $key }}</code></div>
              </td>
              @foreach($roles as $r)
                <td>
                  @if($r->slug === 'admin' || in_array($key, $r->permissions ?? [], true))
                    <i class="bi bi-check-circle-fill text-success"></i>
                  @else
                    <span class="text-muted">&ndash;</span>
                  @endif
                </td>
              @endforeach
              <td class="text-end text-nowrap">
                @if($perm)
                  <a href="{{ route('permissions.edit', $perm) }}" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                  @if($perm->is_system)
                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                            title="The app's code uses this permission, so it cannot be deleted"><i class="bi bi-lock"></i></button>
                  @else
                    <button type="button" class="btn btn-sm btn-danger" title="Delete"
                      data-bs-toggle="modal" data-bs-target="#deleteModal"
                      data-action="{{ route('permissions.destroy', $perm) }}"
                      data-name="the {{ $perm->name }} permission"><i class="bi bi-trash3"></i></button>
                  @endif
                @endif
              </td>
            </tr>
          @endforeach
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection