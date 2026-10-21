@extends('layouts.admin')
@section('title', 'Activity Log')

@section('content')
@php
  $color = ['auth' => 'secondary', 'post' => 'primary', 'comment' => 'info', 'user' => 'warning',
            'role' => 'danger', 'category' => 'success', 'tag' => 'success', 'settings' => 'dark'];
@endphp

<h3 class="mb-3">Activity Log</h3>

<form method="GET" class="row g-2 mb-3">
  <div class="col-md-3">
    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search...">
  </div>
  <div class="col-md-2">
    <select name="user" class="form-select">
      <option value="">All users</option>
      @foreach($users as $u)
        <option value="{{ $u->id }}" @selected(request('user') == $u->id)>{{ $u->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-2">
    <select name="group" class="form-select">
      <option value="">All types</option>
      @foreach($groups as $g)
        <option value="{{ $g }}" @selected(request('group') === $g)>{{ ucfirst($g) }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
  <div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
  <div class="col-md-1 d-grid"><button class="btn btn-dark">Go</button></div>
</form>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead>
        <tr><th>When</th><th>User</th><th>Action</th><th>Details</th><th>IP</th></tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          @php $group = \Illuminate\Support\Str::before($log->action, '.'); @endphp
          <tr>
            <td class="small text-muted text-nowrap" title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
              {{ $log->created_at->format('M d, H:i') }}<br>{{ $log->created_at->diffForHumans() }}
            </td>
            <td>{{ $log->user_name ?? 'Guest' }}</td>
            <td><span class="badge bg-{{ $color[$group] ?? 'secondary' }}">{{ $log->action }}</span></td>
            <td style="min-width:260px">
              {{ $log->description }}
              @if($log->properties)
                <div class="small text-muted">
                  @foreach($log->properties as $k => $v)
                    {{ $k }}: {{ is_array($v) ? json_encode($v) : $v }}@if(! $loop->last) · @endif
                  @endforeach
                </div>
              @endif
            </td>
            <td class="small text-muted">{{ $log->ip_address }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted py-5">No activity yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">{{ $logs->links('pagination::bootstrap-5') }}</div>
@endsection