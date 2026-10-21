<section id="comments" class="comments">
  <h2 class="section-title">Comments <span class="comment-count">{{ $commentCount }}</span></h2>

  @if(session('comment_status'))
    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-1"></i> {{ session('comment_status') }}</div>
  @endif

  @if($post->status === 'approved')
    @include('frontend._comment_form', ['post' => $post, 'parentId' => null])
  @else
    <div class="alert alert-warning">you can put comment approved post.</div>
  @endif

  <div class="comment-list mt-4">
    @forelse($comments as $comment)
      @include('frontend._comment', ['comment' => $comment, 'post' => $post, 'isReply' => false])
    @empty
      <div class="empty-state py-4">
        <i class="bi bi-chat-heart"></i>
        <h5>no comments</h5>
        <p class="text-muted mb-0">may the first comment be from you!</p>
      </div>
    @endforelse
  </div>

  <div class="mt-3 d-flex justify-content-center">
    {{ $comments->links('pagination::bootstrap-5') }}
  </div>
</section>

@push('scripts')
<script>
  
  document.querySelectorAll('[data-reply]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const box = document.getElementById('reply-box-' + btn.dataset.reply);
      box.classList.toggle('d-none');
      if (!box.classList.contains('d-none')) box.querySelector('textarea').focus();
    });
  });

  if (document.querySelector('#comments .is-invalid, #comments .alert')) {
    document.getElementById('comments').scrollIntoView({ behavior: 'smooth' });
  }
</script>
@endpush