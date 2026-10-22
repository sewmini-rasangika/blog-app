@extends('layouts.admin')
@section('title', 'API Tokens')

@section('content')
<div class="row justify-content-center">
  <div class="col-xl-9">

    
    @if(session('new_token'))
      <div class="card shadow-sm mb-4 border-success">
        <div class="p-3">
          <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Token created</strong>
          <div class="small text-muted mb-2">Copy it now. For security it will <strong>not be shown again</strong>.</div>
          <div class="input-group">
            <input type="text" id="newToken" class="form-control font-monospace" value="{{ session('new_token') }}" readonly>
            <button type="button" class="btn btn-primary" id="copyToken"><i class="bi bi-clipboard"></i> Copy</button>
          </div>
        </div>
      </div>
    @endif

    
    <form method="POST" action="{{ route('tokens.store') }}" class="card shadow-sm mb-4">
      @csrf
      <div class="settings-head">
        <span class="stat-icon blue"><i class="bi bi-key"></i></span>
        <div><h5>Create API token</h5><p>Use it with <code>Authorization: Bearer &lt;token&gt;</code> to call the API.</p></div>
      </div>
      <div class="p-4 d-flex gap-2">
        <div class="p-4">
        <div class="row g-2">
            <div class="col-md-6">
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    placeholder="Token name (e.g. Postman, Mobile app)" value="{{ old('name') }}" required maxlength="60">
            </div>
            <div class="col-md-4">
            <input type="date" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror"
                    value="{{ old('expires_at') }}" min="{{ now()->addDay()->toDateString() }}"
                    title="Expiry date (leave empty = never expires)">
            </div>
            <div class="col-md-2 d-grid">
            <button class="btn btn-primary">Generate</button>
            </div>
        </div>
        <div class="form-text">Expiry date optional. Empty nam token eka <strong>never expires</strong>.</div>
        </div>
        @error('name')       <div class="px-4 pb-3 text-danger small">{{ $message }}</div> @enderror
        @error('expires_at') <div class="px-4 pb-3 text-danger small">{{ $message }}</div> @enderror
      </div>
      @error('name') <div class="px-4 pb-3 text-danger small">{{ $message }}</div> @enderror
    </form>

    {{-- Thiyena tokens --}}
    <div class="card shadow-sm">
    <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
        <tr>
            <th>Name</th>
            <th>Created</th>
            <th>Last used</th>
            <th>Expires</th>
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($tokens as $t)
            <tr>
            <td><i class="bi bi-key me-1 text-muted"></i>{{ $t->name }}</td>
            <td class="text-muted small">{{ $t->created_at->diffForHumans() }}</td>
            <td class="text-muted small">{{ $t->last_used_at?->diffForHumans() ?? 'Never' }}</td>
            <td class="small">
                @if(! $t->expires_at)
                <span class="badge bg-secondary-subtle text-secondary">Never</span>
                @elseif($t->expires_at->isPast())
                <span class="badge bg-danger-subtle text-danger">Expired</span>
                @else
                <span class="text-muted" title="{{ $t->expires_at->toDayDateTimeString() }}">{{ $t->expires_at->diffForHumans() }}</span>
                @endif
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-danger"
                data-bs-toggle="modal" data-bs-target="#deleteModal"
                data-action="{{ route('tokens.destroy', $t->id) }}"
                data-name="{{ $t->name }}">Revoke</button>
            </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No API tokens yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <p class="small text-muted mt-3 mb-0">API base URL: <code>{{ url('/api/v1') }}</code></p>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.getElementById('copyToken')?.addEventListener('click', function () {
    const input = document.getElementById('newToken');
    navigator.clipboard.writeText(input.value).then(() => {
      this.innerHTML = '<i class="bi bi-check2"></i> Copied';
    });
  });
</script>
@endpush