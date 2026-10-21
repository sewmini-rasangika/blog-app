@php
  $isSystem = isset($permission) && $permission->is_system;
@endphp

<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Permission key</label>
    <input type="text" name="name" value="{{ old('name', $permission->name ?? '') }}" maxlength="60"
           class="form-control font-monospace @error('name') is-invalid @enderror"
           placeholder="reports.view" @readonly($isSystem)>
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text">
      @if($isSystem)
        This permission is used by the app's code, so the key cannot be changed.
      @else
        Format: <code>module.action</code> (small letters, numbers, underscore). Example: <code>reports.view</code>
      @endif
    </div>
  </div>

  <div class="col-md-6">
    <label class="form-label">Name (shown in the roles page)</label>
    <input type="text" name="display_name" value="{{ old('display_name', $permission->display_name ?? '') }}" maxlength="120"
           class="form-control @error('display_name') is-invalid @enderror" placeholder="View reports">
    @error('display_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-6">
    <label class="form-label">Group</label>
    <input type="text" name="group" value="{{ old('group', $permission->group ?? '') }}" maxlength="40" list="groupList"
           class="form-control @error('group') is-invalid @enderror" placeholder="Posts">
    <datalist id="groupList">
      @foreach($groupNames as $g) <option value="{{ $g }}"> @endforeach
    </datalist>
    @error('group') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text">Pick an existing group or type a new one.</div>
  </div>

  <div class="col-md-6">
    <label class="form-label">Description <span class="text-muted">(optional)</span></label>
    <input type="text" name="description" value="{{ old('description', $permission->description ?? '') }}" maxlength="255"
           class="form-control @error('description') is-invalid @enderror">
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="col-12">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="is_sensitive" value="1" id="isSensitive"
             @checked(old('is_sensitive', $permission->is_sensitive ?? false))>
      <label class="form-check-label" for="isSensitive">Mark as <span class="badge bg-danger">Sensitive</span> (risky permission)</label>
    </div>
  </div>
</div>

<div class="mt-4">
  <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> {{ $button ?? 'Save' }}</button>
  <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
</div>
