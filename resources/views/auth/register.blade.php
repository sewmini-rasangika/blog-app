<x-guest-layout>
  <h1 class="auth-title">Create your account 🚀</h1>
  <p class="auth-sub">Register and start writing your own posts.</p>

  <form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="mb-3">
      <label for="name" class="field-label">Full name</label>
      <div class="field">
        <i class="bi bi-person lead-icon"></i>
        <input id="name" type="text" name="name" value="{{ old('name') }}"
               class="form-control @error('name') is-invalid @enderror"
               placeholder="your name" required autofocus autocomplete="name">
      </div>
      @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label for="email" class="field-label">Email</label>
      <div class="field">
        <i class="bi bi-envelope lead-icon"></i>
        <input id="email" type="email" name="email" value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror"
               placeholder="you@example.com" required autocomplete="username">
      </div>
      @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label for="password" class="field-label">Password</label>
      <div class="field">
        <i class="bi bi-lock lead-icon"></i>
        <input id="password" type="password" name="password"
               class="form-control has-toggle @error('password') is-invalid @enderror"
               placeholder="At least 8 characters" required autocomplete="new-password">
        <button type="button" class="toggle-pass" data-toggle-password="password" aria-label="Show password">
          <i class="bi bi-eye"></i>
        </button>
      </div>
      <div class="strength"><span id="strengthBar"></span></div>
      <small id="strengthText" class="text-muted"></small>
      @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="mb-4">
      <label for="password_confirmation" class="field-label">Confirm password</label>
      <div class="field">
        <i class="bi bi-shield-lock lead-icon"></i>
        <input id="password_confirmation" type="password" name="password_confirmation"
               class="form-control has-toggle" placeholder="again type"
               required autocomplete="new-password">
        <button type="button" class="toggle-pass" data-toggle-password="password_confirmation" aria-label="Show password">
          <i class="bi bi-eye"></i>
        </button>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-auth">Create account <i class="bi bi-arrow-right ms-1"></i></button>
  </form>

  <p class="auth-foot">
    already have an account?
    <a href="{{ route('login') }}" class="auth-link">Login</a>
  </p>

  @push('scripts')
  <script>
    const pw = document.getElementById('password');
    const bar = document.getElementById('strengthBar');
    const txt = document.getElementById('strengthText');
    const levels = [
      { w: '0%',   c: 'transparent', t: '' },
      { w: '25%',  c: '#ef4444',     t: 'Weak' },
      { w: '50%',  c: '#f59e0b',     t: 'Fair' },
      { w: '75%',  c: '#2563eb',     t: 'Good' },
      { w: '100%', c: '#10b981',     t: 'Strong 💪' },
    ];

    pw.addEventListener('input', () => {
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
  </script>
  @endpush
</x-guest-layout>
