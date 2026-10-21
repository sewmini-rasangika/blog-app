@php
  $parentId = $parentId ?? null;
  $mine = (string) old('parent_id') === (string) $parentId;
  $err = fn ($field) => $mine && $errors->has($field);
@endphp

<form method="POST" action="{{ route('comments.store', $post->slug) }}"
      class="{{ $parentId ? 'reply-form' : 'card comment-form shadow-sm' }}">
  @csrf
  @if($parentId)
    <input type="hidden" name="parent_id" value="{{ $parentId }}">
  @endif


  <input type="text" name="website" class="d-none" tabindex="-1" autocomplete="off" aria-hidden="true">

  @guest
    <div class="row g-2 mb-2">
      <div class="col-md-6">
        <input type="text" name="name" value="{{ $mine ? old('name') : '' }}" maxlength="60" required
               class="form-control @if($err('name')) is-invalid @endif" placeholder="your name *">
        @if($err('name')) <div class="invalid-feedback">{{ $errors->first('name') }}</div> @endif
      </div>
      <div class="col-md-6">
        <input type="email" name="email" value="{{ $mine ? old('email') : '' }}" maxlength="120" required
               class="form-control @if($err('email')) is-invalid @endif" placeholder="Email * (not show)">
        @if($err('email')) <div class="invalid-feedback">{{ $errors->first('email') }}</div> @endif
      </div>
    </div>
  @else
    <div class="small text-muted mb-2">
      <i class="bi bi-person-check-fill text-success"></i>
      <strong>{{ auth()->user()->name }}</strong> comment
    </div>
  @endguest

  <textarea name="body" rows="{{ $parentId ? 3 : 4 }}" maxlength="1500" required
            class="form-control @if($err('body')) is-invalid @endif"
            placeholder="{{ $parentId ? 'type reply...' : 'write your idea...' }}">{{ $mine ? old('body') : '' }}</textarea>
  @if($err('body')) <div class="invalid-feedback d-block">{{ $errors->first('body') }}</div> @endif

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2">
    <small class="text-muted">show approved admin</small>
    <button type="submit" class="btn btn-primary btn-sm px-3">
      <i class="bi bi-send-fill me-1"></i> {{ $parentId ? 'Reply' : 'Post comment' }}
    </button>
  </div>
</form>