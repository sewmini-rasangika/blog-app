@extends('layouts.admin')
@section('title', 'New Post')

@section('content')
<h3 class="mb-3">New Post</h3>
<form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="card card-body shadow-sm">
  @csrf
  @include('admin.posts._form', ['button' => 'Publish'])
</form>
@endsection

@push('scripts')
<script>
(function () {
  const box = document.getElementById('serp');
  const site = box.dataset.site;
  const titleInput = document.querySelector('input[name="title"]');
  const editor = document.querySelector('.ql-editor');          


  function cut(text, max) {
    if (text.length <= max) return text;
    let s = text.slice(0, max);
    const sp = s.lastIndexOf(' ');
    if (sp > max * 0.6) s = s.slice(0, sp);
    return s.replace(/[\s,;:.\-–—]+$/, '') + '…';
  }

  
  function makeTitle(t) {
    t = t.trim().replace(/\s+/g, ' ');
    if (!t) return 'Post title appears here';
    const withSite = t + ' | ' + site;
    return withSite.length <= 60 ? withSite : cut(t, 60);
  }

 
  function makeDesc(text) {
    text = text.replace(/\s+/g, ' ').trim();
    if (!text) return 'write content, auto generate description.';
    if (text.length > 155) {
      const m = text.slice(0, 155).match(/^(.{80,}[.!?])(\s|$)/);
      if (m) return m[1];
    }
    return cut(text, 155);
  }

  function render() {
    document.getElementById('serpTitle').textContent = makeTitle(titleInput ? titleInput.value : '');
    document.getElementById('serpDesc').textContent = makeDesc(editor ? editor.innerText : '');
  }

  if (titleInput) titleInput.addEventListener('input', render);
  if (editor) editor.addEventListener('input', render);
  render();
})();
</script>
@endpush