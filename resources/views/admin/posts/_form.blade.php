@php
  $selectedTags = old('tags', isset($post) ? $post->tags->pluck('id')->toArray() : []);
@endphp

<div class="mb-3">
  <label class="form-label">Title</label>
  <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
         value="{{ old('title', $post->title ?? '') }}">
  @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
  <label class="form-label">Category</label>
  <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
    <option value="">-- Select --</option>
    @foreach($categories as $category)
      <option value="{{ $category->id }}"
        @selected(old('category_id', $post->category_id ?? '') == $category->id)>
        {{ $category->name }}
      </option>
    @endforeach
  </select>
  @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
  <label class="form-label d-block">Tags</label>
  @forelse($tags as $tag)
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" name="tags[]" id="tag{{ $tag->id }}"
             value="{{ $tag->id }}" @checked(in_array($tag->id, $selectedTags))>
      <label class="form-check-label" for="tag{{ $tag->id }}">{{ $tag->name }}</label>
    </div>
  @empty
    <span class="text-muted">No Tags  (next step).</span>
  @endforelse
</div>

@php $currentImage = isset($post) && $post->featured ? asset('storage/' . $post->featured) : null; @endphp

<div class="mb-3">
  <label class="form-label">Featured Image</label>

  <label for="featuredInput" class="dropzone @error('featured') is-invalid @enderror" id="dropzone">
    <img id="featuredPreview" src="{{ $currentImage }}" alt="" class="{{ $currentImage ? '' : 'd-none' }}">
    <div class="dz-empty {{ $currentImage ? 'd-none' : '' }}" id="dzEmpty">
      <i class="bi bi-cloud-arrow-up-fill"></i>
      <strong>select image</strong>
      <span>Click and this  drag &amp; drop  · JPG / PNG / WebP · 2MB less</span>
    </div>
    <div class="dz-overlay"><span><i class="bi bi-camera-fill me-1"></i> Change image</span></div>
  </label>

  <input type="file" name="featured" id="featuredInput" accept="image/*" hidden>

  <div class="d-flex justify-content-between align-items-center gap-2 mt-2">
    <small id="featuredInfo" class="text-muted">{{ $currentImage ? 'now image. select new image and change.' : '' }}</small>
    <button type="button" id="featuredClear" class="btn btn-sm btn-outline-danger d-none">
      <i class="bi bi-x-lg"></i> Remove new image
    </button>
  </div>

  @error('featured') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
</div>

@php
  $initial = \App\Support\HtmlSanitizer::clean(old('content', isset($post) ? $post->body_html : ''));
@endphp

<div class="mb-3">
  <label class="form-label">Content</label>
  <div class="editor-wrap @error('content') is-invalid @enderror">
    <div id="editor">{!! $initial !!}</div>
  </div>
  <input type="hidden" name="content" id="contentInput">
  @error('content') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
@endpush


@push('scripts')
<script>
(function () {
  const input = document.getElementById('featuredInput');
  const zone  = document.getElementById('dropzone');
  const img   = document.getElementById('featuredPreview');
  const empty = document.getElementById('dzEmpty');
  const info  = document.getElementById('featuredInfo');
  const clear = document.getElementById('featuredClear');

  const original = img.getAttribute('src') || '';
  const MAX = 2 * 1024 * 1024;
  let previewUrl = null;

  function setInfo(text, cls) {
    info.className = 'small ' + (cls || 'text-muted');
    info.textContent = text;
  }

  
  function showOriginal() {
    if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; }
    if (original) {
      img.src = original;
      img.classList.remove('d-none');
      empty.classList.add('d-none');
      setInfo('now image.');
    } else {
      img.removeAttribute('src');
      img.classList.add('d-none');
      empty.classList.remove('d-none');
      setInfo('');
    }
    clear.classList.add('d-none');
  }

  function handle(file) {
    if (!file) return;

    if (!file.type.startsWith('image/')) {
      input.value = '';
      showOriginal();
      setInfo('select only image file.', 'text-danger');
      return;
    }
    if (file.size > MAX) {
      input.value = '';
      showOriginal();
      setInfo('The image is larger than 2MB (' + (file.size / 1048576).toFixed(1) + ' MB). please select a smaller one.', 'text-danger');
      return;
    }

    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = URL.createObjectURL(file);
    img.src = previewUrl;
    img.classList.remove('d-none');
    empty.classList.add('d-none');
    clear.classList.remove('d-none');
    setInfo(file.name + ' · ' + Math.round(file.size / 1024) + ' KB · It will uploaded until you press save', 'text-success');
  }

  input.addEventListener('change', () => handle(input.files[0]));
  clear.addEventListener('click', () => { input.value = ''; showOriginal(); });


  ['dragenter', 'dragover'].forEach(ev => zone.addEventListener(ev, e => {
    e.preventDefault();
    zone.classList.add('drag');
  }));
  ['dragleave', 'drop'].forEach(ev => zone.addEventListener(ev, e => {
    e.preventDefault();
    zone.classList.remove('drag');
  }));
  zone.addEventListener('drop', e => {
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    handle(file);
  });


  const form = input.closest('form');
  const saveBtn = form.querySelector('button.btn-success');
  const saveHtml = saveBtn ? saveBtn.innerHTML : '';
  form.addEventListener('submit', () => {
    if (!saveBtn) return;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
  });
  window.addEventListener('pageshow', e => {
    if (e.persisted && saveBtn) { saveBtn.disabled = false; saveBtn.innerHTML = saveHtml; }
  });
})();
</script>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
  const quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'This write your story...',
    modules: {
      toolbar: [
        [{ header: [2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ color: [] }, { background: [] }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        [{ align: [] }],
        ['link', 'blockquote'],
        ['clean']
      ]
    }
  });

  const input = document.getElementById('contentInput');
  input.closest('form').addEventListener('submit', function () {
    input.value = quill.getText().trim().length === 0 ? '' : quill.root.innerHTML;
  });
</script>
@endpush

@push('scripts')
<script>
(function () {
  const $ = id => document.getElementById(id);
  const mt = $('metaTitle'), md = $('metaDesc');
  const titleInput = document.querySelector('input[name="title"]');


  function count(el, out, good) {
    const n = el.value.length;
    out.textContent = n + '/' + el.maxLength;
    out.className = n === 0 ? 'text-muted' : (n <= good ? 'count-ok' : 'count-warn');
  }


  function render() {
    $('serpTitle').textContent = mt.value.trim() || (titleInput ? titleInput.value.trim() : '') || 'Post title appears here';
    $('serpDesc').textContent = md.value.trim() || 'If empty meta description,it will be automatically created from the content.';
    count(mt, $('mtCount'), 60);
    count(md, $('mdCount'), 155);
  }

  [mt, md, titleInput].forEach(el => el && el.addEventListener('input', render));
  render();


  $('metaImageInput').addEventListener('change', function () {
    const f = this.files[0], img = $('metaImagePreview');
    if (!f) return;
    if (f.size > 2 * 1024 * 1024) {
      alert('The image is larger thana 2MB.');
      this.value = '';
      return;
    }
    img.src = URL.createObjectURL(f);
    img.classList.remove('d-none');
  });
})();
</script>
@endpush


@php
  $manual = isset($post) ? ($post->meta_manual ?? []) : [];
  $isManual = fn ($f) => in_array($f, $manual, true);
  $metaImageUrl = isset($post) && $post->meta_image ? asset('storage/' . $post->meta_image) : null;


  $autoTitle = isset($post) && ! $isManual('meta_title') ? $post->meta_title : '';
  $autoDesc  = isset($post) && ! $isManual('meta_description') ? $post->meta_description : '';
  $autoKeys  = isset($post) && ! $isManual('meta_keywords') ? $post->meta_keywords : '';

  $seoOpen = $errors->hasAny(['meta_title', 'meta_description', 'meta_keywords', 'meta_image']) || count($manual) > 0;
@endphp

<details class="seo-box mb-4" @if($seoOpen) open @endif>
  <summary>
    <i class="bi bi-magic me-2"></i> SEO &amp; social sharing
    <span class="auto-badge ms-2">Optional: leave empty to auto-generate</span>
  </summary>

  <div class="seo-body">
    <p class="text-muted small">
      You can type your own meta details for this post. Any field you leave <strong>empty</strong>
      is generated automatically from the title, content, tags and featured image.
    </p>

    <div class="serp mb-3" id="serp" data-site="{{ config('seo.site_name') }}">
      <div class="serp-url">{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'yoursite.com' }} &rsaquo; post</div>
      <div class="serp-title" id="serpTitle"></div>
      <div class="serp-desc" id="serpDesc"></div>
    </div>

    <div class="mb-3">
      <label class="form-label d-flex justify-content-between" for="metaTitle">
        <span>Meta title</span><small id="mtCount" class="text-muted"></small>
      </label>
      <input type="text" id="metaTitle" name="meta_title" maxlength="70"
             class="form-control @error('meta_title') is-invalid @enderror"
             value="{{ old('meta_title', $isManual('meta_title') ? $post->meta_title : '') }}"
             placeholder="{{ $autoTitle ?: 'Empty = auto from the post title' }}">
      @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label class="form-label d-flex justify-content-between" for="metaDesc">
        <span>Meta description</span><small id="mdCount" class="text-muted"></small>
      </label>
      <textarea id="metaDesc" name="meta_description" rows="3" maxlength="160"
                class="form-control @error('meta_description') is-invalid @enderror"
                placeholder="{{ $autoDesc ?: 'Empty = auto from the post content' }}">{{ old('meta_description', $isManual('meta_description') ? $post->meta_description : '') }}</textarea>
      @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label class="form-label" for="metaKeywords">Meta keywords <small class="text-muted">(comma separated)</small></label>
      <input type="text" id="metaKeywords" name="meta_keywords" maxlength="255"
             class="form-control @error('meta_keywords') is-invalid @enderror"
             value="{{ old('meta_keywords', $isManual('meta_keywords') ? $post->meta_keywords : '') }}"
             placeholder="{{ $autoKeys ?: 'Empty = auto from tags, category and content' }}">
      @error('meta_keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-1">
      <label class="form-label" for="metaImageInput">Share image <small class="text-muted">(max 2MB, 1200x630 is best)</small></label>
      <input type="file" id="metaImageInput" name="meta_image" accept="image/*"
             class="form-control @error('meta_image') is-invalid @enderror">
      @error('meta_image')<div class="invalid-feedback">{{ $message }}</div>@enderror

      <img id="metaImagePreview" src="{{ $metaImageUrl }}" alt=""
           class="meta-preview mt-2 {{ $metaImageUrl ? '' : 'd-none' }}" style="max-width:260px;border-radius:.6rem;">

      @if($isManual('meta_image'))
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="remove_meta_image" value="1" id="removeMetaImage">
          <label class="form-check-label" for="removeMetaImage">Remove my custom image (use featured image instead)</label>
        </div>
      @else
        <div class="form-text">If you don't upload one, it is generated from the featured image.</div>
      @endif
    </div>
  </div>
</details>

<div>
  <button class="btn btn-success">{{ $button }}</button>
  <a href="{{ route('posts.index') }}" class="btn btn-secondary">Cancel</a>
</div>