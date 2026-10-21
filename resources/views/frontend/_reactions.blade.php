<div class="reactions" id="reactions"
     data-url="{{ route('post.react', $post->slug) }}"
     data-token="{{ csrf_token() }}">
  @foreach(\App\Models\Reaction::TYPES as $type => $emoji)
    <button type="button" class="react-btn {{ $myReaction === $type ? 'active' : '' }}"
            data-type="{{ $type }}" title="{{ ucfirst($type) }}">
      <span class="emoji">{{ $emoji }}</span>
      <span class="count">{{ $reactionCounts[$type] ?? 0 }}</span>
    </button>
  @endforeach
</div>

@push('scripts')
<script>
(function () {
  const box = document.getElementById('reactions');
  if (!box) return;

  box.addEventListener('click', async function (e) {
    const btn = e.target.closest('.react-btn');
    if (!btn || box.dataset.busy) return;
    box.dataset.busy = '1';

    try {
      const res = await fetch(box.dataset.url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': box.dataset.token
        },
        body: JSON.stringify({ type: btn.dataset.type })
      });
      if (!res.ok) throw new Error(res.status);
      const data = await res.json();

      box.querySelectorAll('.react-btn').forEach(function (b) {
        b.classList.toggle('active', b.dataset.type === data.mine);
        b.querySelector('.count').textContent = data.counts[b.dataset.type] ?? 0;
      });
    } catch (err) {
      alert('Reaction eka save una nae. Poddak inna ayeth try karanna.');
    } finally {
      delete box.dataset.busy;
    }
  });
})();
</script>
@endpush