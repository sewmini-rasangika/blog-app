<article class="post-card card h-100">
  <div class="post-thumb">
    <a href="{{ route('post.show', $post->slug) }}" class="d-block h-100" aria-label="{{ $post->title }}">
      @if($post->featured)
        <img src="{{ asset('storage/' . $post->featured) }}" alt="{{ $post->title }}" loading="lazy">
      @else
        <div class="thumb-placeholder"><i class="bi bi-journal-richtext"></i></div>
      @endif
    </a>
    <a href="{{ route('category.show', $post->category->slug) }}" class="cat-pill">{{ $post->category->name }}</a>
  </div>

  <div class="card-body d-flex flex-column">
    <h5 class="post-title">
      <a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a>
    </h5>
    <p class="post-excerpt">{{ $post->excerpt }}</p>

    <div class="post-meta mt-auto">
      <a href="{{ route('author.show', $post->user) }}" class="author">
        <span class="avatar-sm">{{ mb_strtoupper(mb_substr($post->user->name, 0, 1)) }}</span>
        {{ $post->user->name }}
      </a>
      <span>
        {{ $post->created_at->format('M d, Y') }}
        · <i class="bi bi-clock"></i> {{ $post->reading_time }} min
      </span>
    </div>
  </div>
</article>