@extends('layouts.admin')
@section('title', 'My Profile')

@section('content')
@php $user = auth()->user(); @endphp

<div class="row justify-content-center">
  <div class="col-xl-8">

    <form id="profileForm" method="POST" action="{{ route('myprofile.update') }}" enctype="multipart/form-data" class="card shadow-sm">
      @csrf
      @method('PUT')

      <div class="profile-cover"></div>

      <div class="px-4 pb-4">
        <div class="profile-head">
          <label for="avatarInput" class="profile-avatar" title="change photo">
            <img id="avatarPreview" src="{{ $profile->avatar ? asset('storage/' . $profile->avatar) : '' }}"
                 alt="" class="{{ $profile->avatar ? '' : 'd-none' }}">
            <span id="avatarInitial" class="{{ $profile->avatar ? 'd-none' : '' }}">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            <span class="avatar-edit"><i class="bi bi-camera-fill"></i> Change</span>
          </label>

          <div class="who">
            <h4>{{ $user->name }}</h4>
            <div class="text-muted small mb-2">{{ $user->email }}</div>
            <span class="role-pill">{{ $user->isAdmin() ? 'Administrator' : 'Author' }}</span>
            <a href="{{ route('author.show', $user) }}" target="_blank" class="small fw-semibold ms-2">
              View public profile <i class="bi bi-box-arrow-up-right"></i>
            </a>
          </div>
        </div>

        <input type="file" id="avatarInput" name="avatar" accept="image/*" hidden>
        <div id="fileHint" class="small text-muted mb-3">
          Change photo click on circle. (JPG/PNG, 2MB less).
        </div>
        @error('avatar') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

        <div class="mb-3">
          <label class="form-label">About</label>
          <textarea name="about" id="about" rows="5" maxlength="1000" style="min-height:130px"
                    class="form-control @error('about') is-invalid @enderror"
                    placeholder="your self little details...">{{ old('about', $profile->about) }}</textarea>
          <div class="d-flex justify-content-between">
            @error('about') <div class="invalid-feedback d-block">{{ $message }}</div> @else <span></span> @enderror
            <small class="text-muted"><span id="aboutCount">0</span>/1000</small>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label">Facebook URL</label>
          <input type="url" name="facebook" value="{{ old('facebook', $profile->facebook) }}"
                 class="form-control @error('facebook') is-invalid @enderror"
                 placeholder="https://facebook.com/yourname">
          @error('facebook') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <button type="submit" id="saveBtn" class="btn btn-primary w-100 py-2 fw-semibold">
          <i class="bi bi-check2-circle me-1"></i> Update Profile
        </button>
      </div>
    </form>

  </div>
</div>
@endsection

@push('scripts')
<script>
  const input   = document.getElementById('avatarInput');
  const preview = document.getElementById('avatarPreview');
  const initial = document.getElementById('avatarInitial');
  const hint    = document.getElementById('fileHint');


  input.addEventListener('change', () => {
    const f = input.files[0];
    if (!f) return;

    if (f.size > 2 * 1024 * 1024) {
      hint.innerHTML = '<span class="text-danger">The photo is larger than 2MB. Please select a smaller one.</span>';
      input.value = '';
      return;
    }

    preview.src = URL.createObjectURL(f);
    preview.classList.remove('d-none');
    initial.classList.add('d-none');
    hint.innerHTML = '<i class="bi bi-image"></i> ' + f.name + ' <span class="text-success">· It will not be saved untillyou click update</span>';
  });


  const about = document.getElementById('about');
  const count = document.getElementById('aboutCount');
  const updateCount = () => count.textContent = about.value.length;
  about.addEventListener('input', updateCount);
  updateCount();

 
  document.getElementById('profileForm').addEventListener('submit', () => {
    const b = document.getElementById('saveBtn');
    b.disabled = true;
    b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
  });
</script>
@endpush