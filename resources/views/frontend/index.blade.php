@extends('layouts.frontend')
@section('title', 'My Blog')

@section('content')
@if($posts->isEmpty())
  <div class="empty-state">
    <i class="bi bi-journal-x"></i>
    <h5>  No posts</h5>
    <p class="text-muted">Be the first person to write the first post!</p>
    @guest
      <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
    @else
      <a href="{{ route('posts.create') }}" class="btn btn-primary">+ New Post</a>
    @endguest
  </div>
@else
  @php
    $hero = $posts->onFirstPage() ? $posts->first() : null;
    $rest = $hero ? $posts->slice(1) : $posts;
  @endphp


  @if($hero)
    <a href="{{ route('post.show', $hero->slug) }}" class="hero-feature">
      <div class="hero-bg {{ $hero->featured ? '' : 'no-img' }}"
           @if($hero->featured) style="background-image:url('{{ asset('storage/' . $hero->featured) }}')" @endif></div>

      <div class="hero-body">
        <span class="hero-chip"><i class="bi bi-stars"></i> Latest · {{ $hero->category->name }}</span>
        <h2 class="hero-title">{{ $hero->title }}</h2>
        <p class="hero-excerpt d-none d-md-block">{{ $hero->excerpt }}</p>

        <div class="hero-meta">
          <span class="d-inline-flex align-items-center gap-2">
            <span class="avatar-sm on-dark">{{ mb_strtoupper(mb_substr($hero->user->name, 0, 1)) }}</span>
            {{ $hero->user->name }}
          </span>
          <span><i class="bi bi-calendar3"></i> {{ $hero->created_at->format('M d, Y') }}</span>
          <span><i class="bi bi-clock"></i> {{ $hero->reading_time }} min read</span>
          <span class="hero-cta">Read more <i class="bi bi-arrow-right"></i></span>
        </div>
      </div>
    </a>
  @endif


  @if($rest->isNotEmpty())
    <h2 class="section-title">{{ $hero ? 'Latest Posts' : 'More Posts' }}</h2>

    <div class="row g-4">
      @foreach($rest as $post)
        <div class="col-md-6">@include('frontend._card', ['post' => $post])</div>
      @endforeach
    </div>
  @endif

  <div class="mt-4 d-flex justify-content-center">
    {{ $posts->links('pagination::bootstrap-5') }}
  </div>
@endif
@endsection