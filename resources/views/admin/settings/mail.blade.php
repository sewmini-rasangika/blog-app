@extends('layouts.admin')
@section('title', 'Mail settings')

@section('content')
<div class="row justify-content-center">
  <div class="col-xl-8">

    
    <div class="card shadow-sm mb-4">
      <div class="p-3 d-flex align-items-center gap-3">
        <span class="stat-icon {{ $saved['enabled'] ? 'green' : 'violet' }}" style="width:44px;height:44px;border-radius:.9rem;font-size:1.2rem">
          <i class="bi {{ $saved['enabled'] ? 'bi-check2-circle' : 'bi-file-earmark-code' }}"></i>
        </span>
        <div>
          @if($saved['enabled'])
            <strong>Custom SMTP is ON</strong>
            <div class="small text-muted">send mails<strong>{{ $activeHost }}</strong> and (this page is settings).</div>
          @else
            <strong>use .env settings</strong>
            <div class="small text-muted">Mailer: <strong>{{ $activeMailer }}</strong>{{ $activeHost ? ' · ' . $activeHost : '' }}</div>
          @endif
        </div>
      </div>
    </div>

    <form method="POST" action="{{ route('settings.mail.update') }}" class="card shadow-sm" autocomplete="off">
      @csrf

      <div class="settings-head">
        <span class="stat-icon blue"><i class="bi bi-envelope-at"></i></span>
        <div><h5>SMTP configuration</h5><p>The server that send emails like reset password, notifications.</p></div>
      </div>

      <div class="p-4">

       
        <div class="form-check form-switch mb-4">
          <input type="hidden" name="smtp_enabled" value="0">
          <input class="form-check-input" type="checkbox" role="switch" id="smtpEnabled" name="smtp_enabled" value="1"
                 @checked(old('smtp_enabled', $saved['enabled'] ? '1' : '0') == '1')>
          <label class="form-check-label fw-semibold" for="smtpEnabled">use this setting (override .env settings)</label>
        </div>

       
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
          <span class="text-muted small">Quick fill:</span>
          <button type="button" class="preset-chip" data-preset="gmail">Gmail</button>
          <button type="button" class="preset-chip" data-preset="outlook">Outlook</button>
          <button type="button" class="preset-chip" data-preset="brevo">Brevo</button>
          <button type="button" class="preset-chip" data-preset="mailtrap">Mailtrap</button>
        </div>

       
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">SMTP host</label>
            <input type="text" name="smtp_host" id="smtpHost" value="{{ old('smtp_host', $saved['host']) }}"
                   class="form-control @error('smtp_host') is-invalid @enderror" placeholder="smtp.gmail.com" autocomplete="off">
            @error('smtp_host') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-3">
            <label class="form-label">Port</label>
            <input type="number" name="smtp_port" id="smtpPort" value="{{ old('smtp_port', $saved['port']) }}"
                   class="form-control @error('smtp_port') is-invalid @enderror" placeholder="587">
            @error('smtp_port') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-3">
            <label class="form-label">Encryption</label>
            <select name="smtp_encryption" id="smtpEnc" class="form-select @error('smtp_encryption') is-invalid @enderror">
              @foreach(['tls' => 'TLS / STARTTLS (587)', 'ssl' => 'SSL (465)', 'none' => 'None'] as $k => $label)
                <option value="{{ $k }}" @selected(old('smtp_encryption', $saved['encryption']) === $k)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>

       
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Username</label>
            <input type="text" name="smtp_username" value="{{ old('smtp_username', $saved['username']) }}"
                   class="form-control" placeholder="you@gmail.com" autocomplete="off">
          </div>
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <div class="input-group">
              <input type="password" name="smtp_password" id="smtpPass" autocomplete="new-password"
                     class="form-control" placeholder="{{ $hasPassword ? '•••••••• (saved. empty = no change)' : 'App password / SMTP password' }}">
              <button type="button" class="btn btn-eye" data-toggle-pass="smtpPass"><i class="bi bi-eye"></i></button>
            </div>
            @if($hasPassword)
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="clear_password" value="1" id="clearPass">
                <label class="form-check-label small" for="clearPass">delete the saved password.</label>
              </div>
            @endif
          </div>
        </div>

        {{-- Sender --}}
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label">From address</label>
            <input type="email" name="smtp_from_address" value="{{ old('smtp_from_address', $saved['from_address']) }}"
                   class="form-control @error('smtp_from_address') is-invalid @enderror" placeholder="noreply@yourdomain.com">
            @error('smtp_from_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">From name</label>
            <input type="text" name="smtp_from_name" value="{{ old('smtp_from_name', $saved['from_name']) }}"
                   class="form-control" placeholder="{{ config('app.name') }}">
          </div>
        </div>

        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2-circle me-1"></i> Save settings</button>

        <hr class="my-4">

      
        <h6 class="mb-1"><i class="bi bi-send me-1"></i> Send a test email</h6>
        <p class="text-muted small">It sends a test using the values currently in the form(even if not saved yet).</p>
        <div class="row g-2">
          <div class="col-md-8">
            <input type="email" name="test_to" value="{{ old('test_to', auth()->user()->email) }}"
                   class="form-control @error('test_to') is-invalid @enderror" placeholder="The address where the test mail should be sent">
            @error('test_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-4">
            <button type="submit" formaction="{{ route('settings.mail.test') }}" class="btn btn-outline-primary w-100">
              <i class="bi bi-send me-1"></i> Send test
            </button>
          </div>
        </div>

      </div>
    </form>

  </div>
</div>
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


  const presets = {
    gmail:    ['smtp.gmail.com', 587, 'tls'],
    outlook:  ['smtp.office365.com', 587, 'tls'],
    brevo:    ['smtp-relay.brevo.com', 587, 'tls'],
    mailtrap: ['sandbox.smtp.mailtrap.io', 2525, 'tls'],
  };
  document.querySelectorAll('[data-preset]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const p = presets[btn.dataset.preset];
      document.getElementById('smtpHost').value = p[0];
      document.getElementById('smtpPort').value = p[1];
      document.getElementById('smtpEnc').value = p[2];
    });
  });


  document.getElementById('smtpEnc').addEventListener('change', function () {
    const port = document.getElementById('smtpPort');
    if (this.value === 'ssl' && ['587', '2525', ''].includes(port.value)) port.value = 465;
    if (this.value === 'tls' && port.value === '465') port.value = 587;
  });
</script>
@endpush