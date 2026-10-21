<x-guest-layout>
  <h1 class="auth-title">New password 🔒</h1>
  <p class="auth-sub">Enter your new password.</p>

  <form method="POST" action="{{ route('password.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="mb-3">
      <label for="email" class="field-label">Email</label>
      <div class="field">
        <i class="bi bi-envelope lead-icon"></i>
        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
               class="form-control @error('email') is-invalid @enderror" required autocomplete="username">
      </div>
      @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label for="password" class="field-label">New password</label>
      <div class="field">
        <i class="bi bi-lock lead-icon"></i>
        <input id="password" type="password" name="password"
               class="form-control has-toggle @error('password') is-invalid @enderror"
               required autocomplete="new-password">
        <button type="button" class="toggle-pass" data-toggle-password="password" aria-label="Show password">
          <i class="bi bi-eye"></i>
        </button>
      </div>
      @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="mb-4">
      <label for="password_confirmation" class="field-label">Confirm password</label>
      <div class="field">
        <i class="bi bi-shield-lock lead-icon"></i>
        <input id="password_confirmation" type="password" name="password_confirmation"
               class="form-control" required autocomplete="new-password">
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-auth">Reset password</button>
  </form>
</x-guest-layout>
