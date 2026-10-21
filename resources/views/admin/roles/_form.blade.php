@php
  $readonly = $readonly ?? false;
  $isAdminRole = isset($role) && $role->slug === 'admin';
  $allKeys = collect($groups)->collapse()->keys()->all();
  $selected = $isAdminRole ? $allKeys : old('permissions', $role->permissions ?? []);
@endphp

<fieldset @disabled($readonly)>
  <div class="row g-3 mb-4">
    <div class="col-md-5">
      <label class="form-label">Role name</label>
      <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" maxlength="60"
             class="form-control @error('name') is-invalid @enderror" placeholder="Editor">
      @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-7">
      <label class="form-label">Description</label>
      <input type="text" name="description" value="{{ old('description', $role->description ?? '') }}" maxlength="255"
             class="form-control @error('description') is-invalid @enderror" placeholder="what is this role">
      @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
  </div>

  @foreach($groups as $group => $perms)
    <div class="card mb-3">
      <div class="queue-head">
        <h5>{{ $group }}</h5>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="grp{{ $loop->index }}" data-group-toggle="{{ $loop->index }}">
          <label class="form-check-label small" for="grp{{ $loop->index }}">Select all</label>
        </div>
      </div>
      <div class="p-3">
        @foreach($perms as $key => $label)
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}"
                   id="p_{{ $loop->parent->index }}_{{ $loop->index }}" data-group="{{ $loop->parent->index }}"
                   @checked(in_array($key, $selected, true))>
            <label class="form-check-label" for="p_{{ $loop->parent->index }}_{{ $loop->index }}">
              {{ $label }}
              @if(\App\Support\Permissions::isSensitive($key))
                <span class="badge bg-danger ms-1" title="this is a risk permission">Sensitive</span>
              @endif
              <span class="small text-muted ms-1"><code>{{ $key }}</code></span>
            </label>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach
  @error('permissions.*') <div class="text-danger small mb-3">{{ $message }}</div> @enderror
</fieldset>

<div class="mt-3">
  @unless($readonly)
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> {{ $button ?? 'Save' }}</button>
  @endunless
  <a href="{{ route('roles.index') }}" class="btn btn-secondary">{{ $readonly ? 'Back' : 'Cancel' }}</a>
</div>

@push('scripts')
<script>
  
  document.querySelectorAll('[data-group-toggle]').forEach(function (master) {
    const boxes = document.querySelectorAll('[data-group="' + master.dataset.groupToggle + '"]');
    const sync = () => master.checked = [...boxes].every(b => b.checked);
    sync();
    boxes.forEach(b => b.addEventListener('change', sync));
    master.addEventListener('change', () => boxes.forEach(b => b.checked = master.checked));
  });
</script>
@endpush