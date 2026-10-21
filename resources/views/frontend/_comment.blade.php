@php
  $avatar = $comment->user?->profile?->avatar;
  $role = $comment->user?->isAdmin() ? 'Admin' : ($comment->user_id && $comment->user_id === $post->user_id ? 'Author' : null);
@endphp

<div class="comment {{ $isReply ? 'is-reply' : '' }}" id="comment-{{ $comment->id }}">
  <span class="c-avatar">
    @if($avatar)
      <img src="{{ asset('storage/' . $avatar) }}" alt="" loading="lazy">
    @else
      {{ mb_strtoupper(mb_substr($comment->name, 0, 1)) }}
    @endif
  </span>

  <div class="c-body">
    <div class="c-head">
      <strong>{{ $comment->name }}</strong>
      @if($role) <span class="c-badge">{{ $role }}</span> @endif
      <span class="c-time">{{ $comment->created_at->diffForHumans() }}</span>
    </div>

    <p class="c-text">{!! nl2br(e($comment->body)) !!}</p>

    @unless($isReply)
      @if($post->status === 'approved')
        <button type="button" class="c-reply-btn" data-reply="{{ $comment->id }}">
          <i class="bi bi-reply-fill"></i> Reply
        </button>

        <div class="reply-box {{ (string) old('parent_id') === (string) $comment->id ? '' : 'd-none' }}" id="reply-box-{{ $comment->id }}">
          @include('frontend._comment_form', ['post' => $post, 'parentId' => $comment->id])
        </div>
      @endif

      @foreach($comment->replies as $reply)
        @include('frontend._comment', ['comment' => $reply, 'post' => $post, 'isReply' => true])
      @endforeach
    @endunless
  </div>
</div>
@php
  $avatar = $comment->user?->profile?->avatar;
  $role = $comment->user?->isAdmin() ? 'Admin' : ($comment->user_id && $comment->user_id === $post->user_id ? 'Author' : null);
@endphp

<div class="comment {{ $isReply ? 'is-reply' : '' }}" id="comment-{{ $comment->id }}">
  @if($comment->replies->count() > 0)
    <div class="ml-8 mt-4">
        @foreach($comment->replies as $reply)
            @include('frontend._comment', ['comment' => $reply, 'post' => $post, 'isReply' => true])
        @endforeach
    </div>
@endif
</div>
