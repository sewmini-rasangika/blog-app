<x-guest-layout>
  <h1 class="auth-title">Welcome back 👋</h1>
  <p class="auth-sub">Login to your account.</p>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
      <label for="email" class="field-label">Email</label>
      <div class="field">
        <i class="bi bi-envelope lead-icon"></i>
        <input id="email" type="email" name="email" value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror"
               placeholder="you@example.com" required autofocus autocomplete="username">
      </div>
      @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label for="password" class="field-label">Password</label>
      <div class="field">
        <i class="bi bi-lock lead-icon"></i>
        <input id="password" type="password" name="password"
               class="form-control has-toggle @error('password') is-invalid @enderror"
               placeholder="••••••••" required autocomplete="current-password">
        <button type="button" class="toggle-pass" data-toggle-password="password" aria-label="Show password">
          <i class="bi bi-eye"></i>
        </button>
      </div>
      @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="remember" id="remember">
        <label class="form-check-label small" for="remember">Remember me</label>
      </div>
      @if(Route::has('password.request'))
        <a href="{{ route('password.request') }}" class="small auth-link">Forgot password?</a>
      @endif
    </div>

    <button type="submit" class="btn btn-primary btn-auth">Login <i class="bi bi-arrow-right ms-1"></i></button>
  </form>

  <p class="auth-foot">
    Don't have an account?
    <a href="{{ route('register') }}" class="auth-link">Register</a>
  </p>
</x-guest-layout>
