@extends('layouts.frontend')

@section('title', $post->seo_title)

@section('seo')
  @include('partials.seo', [
    'seoTitle'       => $post->seo_title,
    'seoDescription' => $post->seo_description,
    'seoKeywords'    => $post->seo_keywords,
    'seoImage'       => $post->seo_image,
    'seoType'        => 'article',
    'seoUrl'         => route('post.show', $post->slug),
    'seoNoindex'     => $post->status !== 'approved',    // pending/rejected preview එක Google ට index කරන්න එපා
    'seoJsonLd'      => $post->seoJsonLd(),
  ])
@endsection

@section('content')
<div class="read-progress" id="readBar"></div>

@if($post->status !== 'approved')
  <div class="alert alert-warning">
    <i class="bi bi-eye-fill me-1"></i>
    This <strong>{{ ucfirst($post->status) }}</strong> is a post. Only visible to you(author/admin).
  </div>
@endif


<nav class="crumbs" aria-label="breadcrumb">
  <a href="{{ route('home') }}"><i class="bi bi-house-door-fill"></i> Home</a>
  <i class="bi bi-chevron-right"></i>
  <a href="{{ route('category.show', $post->category->slug) }}">{{ $post->category->name }}</a>
  <i class="bi bi-chevron-right"></i>
  <span class="current">{{ $post->title }}</span>
</nav>


<article class="card article shadow-sm mb-4">
  <a href="{{ route('category.show', $post->category->slug) }}" class="article-cat align-self-start">{{ $post->category->name }}</a>
  <h1 class="article-title">{{ $post->title }}</h1>

  <div class="article-meta">
    <a href="{{ route('author.show', $post->user) }}" class="by">
      <span class="avatar-sm">
        @if($post->user->profile?->avatar)
          <img src="{{ asset('storage/' . $post->user->profile->avatar) }}" alt="">
        @else
          {{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}
        @endif
      </span>
      {{ $post->user->name }}
    </a>
    <span><i class="bi bi-calendar3"></i> {{ $post->created_at->format('M d, Y') }}</span>
    <span><i class="bi bi-clock"></i> {{ $post->reading_time }} min read</span>
    <span><i class="bi bi-eye"></i> {{ number_format($post->views) }} views</span>
  </div>

  @if($post->featured)
    <figure class="post-cover">
      <img src="{{ asset('storage/' . $post->featured) }}" alt="{{ $post->title }}">
    </figure>
  @endif

 <div class="post-content">{!! $post->body_html !!}</div>

  @if($post->tags->isNotEmpty())
    <div class="tag-row">
      <i class="bi bi-tags-fill text-muted"></i>
      @foreach($post->tags as $tag)
        <a href="{{ route('tag.show', $tag->slug) }}" class="tag-chip">#{{ $tag->name }}</a>
      @endforeach
    </div>
  @endif
    @if($post->tags->isNotEmpty())

  @endif

  {{-- ===== Reactions ===== --}}
  @if($post->status === 'approved')
    @include('frontend._reactions')
  @endif

    @php
    $shareUrl = urlencode(url()->current());
    $shareTitle = urlencode($post->title);
  @endphp
  <div class="share-bar">
    <strong><i class="bi bi-share-fill me-1"></i>Share</strong>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="btn-share fb"><i class="bi bi-facebook"></i> Facebook</a>
    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener" class="btn-share x"><i class="bi bi-twitter-x"></i> X</a>
    <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}" target="_blank" rel="noopener" class="btn-share wa"><i class="bi bi-whatsapp"></i> WhatsApp</a>
    <button type="button" class="btn-share copy" id="copyLink"><i class="bi bi-link-45deg"></i> Copy link</button>
  </div>
</article>


<div class="card author-box shadow-sm mb-4">
  <div class="author-avatar">
    @if($post->user->profile?->avatar)
      <img src="{{ asset('storage/' . $post->user->profile->avatar) }}" alt="{{ $post->user->name }}">
    @else
      {{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}
    @endif
  </div>
  <div>
    <small class="text-muted text-uppercase fw-semibold" style="letter-spacing:.8px">Written by</small>
    <h6>{{ $post->user->name }}</h6>
    <p class="about">{{ $post->user->profile?->about ?: 'this author no detais.' }}</p>
    <a href="{{ route('author.show', $post->user) }}" class="btn btn-primary btn-sm">More posts</a>
    @if($post->user->profile?->facebook)
      <a href="{{ $post->user->profile->facebook }}" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-facebook"></i>
      </a>
    @endif
  </div>
</div>


@if($prev || $next)
  <div class="row g-3 mb-4">
    <div class="col-sm-6">
      @if($prev)
        <a href="{{ route('post.show', $prev->slug) }}" class="nav-post">
          <small><i class="bi bi-arrow-left"></i> Previous</small>
          <span>{{ \Illuminate\Support\Str::limit($prev->title, 60) }}</span>
        </a>
      @endif
      
    </div>
    <div class="col-sm-6">
      @if($next)
        <a href="{{ route('post.show', $next->slug) }}" class="nav-post next">
          <small>Next <i class="bi bi-arrow-right"></i></small>
          <span>{{ \Illuminate\Support\Str::limit($next->title, 60) }}</span>
        </a>
      @endif
    </div>
  </div>
@endif

@include('frontend._comments')



@if($related->isNotEmpty())
  <h2 class="section-title mt-5">You may also like</h2>
  <div class="row g-4">
    @foreach($related as $rel)
      <div class="col-md-6 col-xl-4">@include('frontend._card', ['post' => $rel])</div>
    @endforeach
  </div>
@endif
@endsection

@push('scripts')
<script>
  
  const bar = document.getElementById('readBar');
  const updateBar = () => {
    const h = document.documentElement;
    const max = h.scrollHeight - h.clientHeight;
    bar.style.width = (max > 0 ? (h.scrollTop / max) * 100 : 0) + '%';
  };
  window.addEventListener('scroll', updateBar, { passive: true });
  updateBar();

  document.getElementById('copyLink').addEventListener('click', function () {
    navigator.clipboard.writeText(window.location.href).then(() => {
      const old = this.innerHTML;
      this.innerHTML = '<i class="bi bi-check2"></i> Copied!';
      setTimeout(() => this.innerHTML = old, 2000);
    });
  });
</script>
@endpush