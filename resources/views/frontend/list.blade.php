@extends('layouts.frontend')
@section('title', $title)

@section('seo')
  @include('partials.seo', [
    'seoTitle'   => $title,
    'seoNoindex' => request()->routeIs('search'),
  ])
@endsection

@section('content')
<div class="page-head">
  <h3>{{ $title }}</h3>
  <p>{{ $posts->total() }} {{ \Illuminate\Support\Str::plural('post', $posts->total()) }}</p>
</div>

@if($posts->isEmpty())
  <div class="empty-state">
    <i class="bi bi-search"></i>
    <h5>No posts found</h5>
    <p class="text-muted mb-3">Try searching with another kerword, or go to back to home.</p>
    <a href="{{ route('home') }}" class="btn btn-primary">← Home</a>
  </div>
@else
  <div class="row g-4">
    @foreach($posts as $post)
      <div class="col-md-6">@include('frontend._card', ['post' => $post])</div>
    @endforeach
  </div>

  <div class="mt-4 d-flex justify-content-center">
    {{ $posts->links('pagination::bootstrap-5') }}
  </div>
@endif
@endsection