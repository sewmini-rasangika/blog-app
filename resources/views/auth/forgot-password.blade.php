<x-guest-layout>
  <h1 class="auth-title">Forgot password? 🔑</h1>
  <p class="auth-sub">Enter your email. We will send a password reset link.</p>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-4">
      <label for="email" class="field-label">Email</label>
      <div class="field">
        <i class="bi bi-envelope lead-icon"></i>
        <input id="email" type="email" name="email" value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror"
               placeholder="you@example.com" required autofocus>
      </div>
      @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <button type="submit" class="btn btn-primary btn-auth">Send reset link</button>
  </form>

  <p class="auth-foot">
    <a href="{{ route('login') }}" class="auth-link"><i class="bi bi-arrow-left"></i> Back to login</a>
  </p>
</x-guest-layout>
