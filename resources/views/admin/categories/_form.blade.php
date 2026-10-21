<div class="mb-3">
  <label class="form-label">Category Name</label>
  <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
         value="{{ old('name', $category->name ?? '') }}">
  @error('name')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>
<div>
  <button class="btn btn-success">{{ $button }}</button>
  <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
</div>