@extends('layouts.admin')
@section('title', 'Account settings')

@section('content')
@php
  $user = auth()->user();
  $user->loadMissing('profile');
  $avatar = $user->profile?->avatar;
@endphp

<div class="row justify-content-center">
  <div class="col-xl-8">

   
    <div class="card shadow-sm mb-4">
      <div class="profile-cover"></div>
      <div class="px-4 pb-4">
        <div class="profile-head mb-0">
          <div class="profile-avatar" style="cursor:default">
            @if($avatar)
              <img src="{{ asset('storage/' . $avatar) }}" alt="">
            @else
              <span>{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            @endif
          </div>
          <div class="who">
            <h4>{{ $user->name }}</h4>
            <div class="text-muted small mb-2">{{ $user->email }}</div>
            <span class="role-pill">{{ $user->isAdmin() ? 'Administrator' : 'Author' }}</span>
            <span class="text-muted small ms-2">Member since {{ $user->created_at->format('M Y') }}</span>
          </div>
          <a href="{{ route('myprofile.edit') }}" class="btn btn-sm btn-outline-primary ms-auto mb-2">
            <i class="bi bi-person-circle me-1"></i> Edit public profile
          </a>
        </div>
      </div>
    </div>

   
    <div class="card shadow-sm mb-4">
      <div class="settings-head">
        <span class="stat-icon blue"><i class="bi bi-person-vcard"></i></span>
        <div><h5>Account details</h5><p>your name and login email.</p></div>
      </div>
      <form method="POST" action="{{ route('profile.update') }}" class="p-4" data-loading-form>
        @csrf
        @method('PATCH')
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"
                   class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-4"><i class="bi bi-check2-circle me-1"></i> Save changes</button>
      </form>
    </div>

    {{-- Change password --}}
    <div class="card shadow-sm mb-4">
      <div class="settings-head">
        <span class="stat-icon violet"><i class="bi bi-shield-lock-fill"></i></span>
        <div><h5>Change password</h5><p>To security, use long password.</p></div>
      </div>
      <form method="POST" action="{{ route('password.update') }}" class="p-4" data-loading-form>
        @csrf
        @method('PUT')

        <div class="mb-3">
          <label class="form-label">Current password</label>
          <div class="input-group">
            <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror">
            <button type="button" class="btn btn-eye" data-toggle-pass="current_password"><i class="bi bi-eye"></i></button>
          </div>
          @error('current_password', 'updatePassword') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">New password</label>
            <div class="input-group">
              <input type="password" id="new_password" name="password" autocomplete="new-password"
                     class="form-control @error('password', 'updatePassword') is-invalid @enderror">
              <button type="button" class="btn btn-eye" data-toggle-pass="new_password"><i class="bi bi-eye"></i></button>
            </div>
            <div class="strength"><span id="strengthBar"></span></div>
            <small id="strengthText" class="text-muted"></small>
            @error('password', 'updatePassword') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Confirm new password</label>
            <div class="input-group">
              <input type="password" id="confirm_password" name="password_confirmation" autocomplete="new-password" class="form-control">
              <button type="button" class="btn btn-eye" data-toggle-pass="confirm_password"><i class="bi bi-eye"></i></button>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4"><i class="bi bi-key-fill me-1"></i> Update password</button>
      </form>
    </div>

   
    <div class="card shadow-sm danger-zone mb-4">
      <div class="settings-head">
        <span class="stat-icon rose"><i class="bi bi-exclamation-triangle-fill"></i></span>
        <div><h5>Delete account</h5><p>This action cannot be undone..</p></div>
      </div>
      <div class="p-4">
        @if($user->isAdmin())
          <p class="mb-0 text-muted"><i class="bi bi-info-circle me-1"></i>  Administrator account cannot be deleted..</p>
        @else
          <p class="text-muted">If you delete your account, <strong>All posts and your profile will be permanently deleted forever.</strong>.</p>
          <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
            <i class="bi bi-trash3 me-1"></i> Delete my account
          </button>
        @endif
      </div>
    </div>

  </div>
</div>

@unless($user->isAdmin())
  <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" action="{{ route('profile.destroy') }}" class="modal-content confirm-modal">
        @csrf
        @method('DELETE')
        <div class="modal-body text-center p-4 p-md-5">
          <div class="confirm-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
          <h5 class="fw-bold mb-2">is delete account?</h5>
          <p class="text-muted mb-3">enter your password conform.</p>

          <input type="password" name="password" placeholder="Password" autocomplete="current-password"
                 class="form-control text-center @error('password', 'userDeletion') is-invalid @enderror">
          @error('password', 'userDeletion') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

          <div class="d-flex gap-2 justify-content-center mt-4">
            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger px-4">Yes, delete everything</button>
          </div>
        </div>
      </form>
    </div>
  </div>
@endunless
@endsection

@push('scripts')
<script>
  
  document.querySelectorAll('[data-toggle-pass]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const input = document.getElementById(btn.dataset.togglePass);
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
  });

  (function () {
    const pw = document.getElementById('new_password');
    const bar = document.getElementById('strengthBar');
    const txt = document.getElementById('strengthText');
    const levels = [
      { w: '0%',   c: 'transparent', t: '' },
      { w: '25%',  c: '#ef4444',     t: 'Weak' },
      { w: '50%',  c: '#f59e0b',     t: 'Fair' },
      { w: '75%',  c: '#2563eb',     t: 'Good' },
      { w: '100%', c: '#10b981',     t: 'Strong 💪' },
    ];
    pw.addEventListener('input', function () {
      const v = pw.value;
      let s = 0;
      if (v.length >= 8) s++;
      if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
      if (/\d/.test(v)) s++;
      if (/[^A-Za-z0-9]/.test(v)) s++;
      if (v.length === 0) s = 0;
      bar.style.width = levels[s].w;
      bar.style.background = levels[s].c;
      txt.textContent = levels[s].t;
    });
  })();

 
  document.querySelectorAll('[data-loading-form]').forEach(function (form) {
    form.addEventListener('submit', function () {
      const b = form.querySelector('button[type=submit]');
      b.disabled = true;
      b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    });
  });


  @if($errors->userDeletion->isNotEmpty())
    new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
  @endif
</script>
@endpush