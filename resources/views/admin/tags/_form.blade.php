<div class="mb-3">
  <label class="form-label">Tag Name</label>
  <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
         value="{{ old('name', $tag->name ?? '') }}">
  @error('name')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>
<div>
  <button class="btn btn-success">{{ $button }}</button>
  <a href="{{ route('tags.index') }}" class="btn btn-secondary">Cancel</a>
</div>