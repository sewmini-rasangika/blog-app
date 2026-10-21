<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Support\Activity;
use Illuminate\Http\Request;

class CommentController extends Controller
{

   public function store(Request $request, Post $post)
{
    abort_unless($post->status === 'approved', 404);

  
    if ($request->filled('website')) {
        return $this->backToPost($post)->with('comment_status', 'thank you!');
    }

    $user = $request->user();   

    $data = $request->validate([
        'name' => $user ? 'nullable' : 'required|string|max:60',
        'email' => $user ? 'nullable' : 'required|email|max:120',
        'body' => 'required|string|min:2|max:1500',
        'parent_id' => 'nullable|integer',
    ]);

    $parentId = null;
    if (! empty($data['parent_id'])) {
        $parent = Comment::approved()
            ->where('post_id', $post->id)
            ->whereNull('parent_id')
            ->find($data['parent_id']);

        abort_unless($parent, 404);
        $parentId = $parent->id;
    }


    $canModerate = $user?->can('comments.moderate') ?? false;

    Comment::create([
        'post_id' => $post->id,
        'user_id' => $user?->id,
        'parent_id' => $parentId,
        'name' => $user?->name ?? trim($data['name']),
        'email' => $user?->email ?? trim($data['email']),
        'body' => trim($data['body']),
        'status' => $canModerate ? 'approved' : 'pending',
    ]);

    return $this->backToPost($post)->with(
        'comment_status',
        $canModerate ? 'posted comment.' : 'thank you! your comment show approved admin 🙌'
    );
}



    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $status = in_array($status, ['all', 'pending', 'approved', 'spam'], true) ? $status : 'pending';

        $counts = Comment::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $like = '%' . addcslashes(trim((string) $request->input('q')), '%_\\') . '%';

        $comments = Comment::with(['post:id,title,slug,deleted_at', 'parent:id,name'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->filled('q'), fn ($q) => $q->where(
                fn ($w) => $w->where('body', 'like', $like)->orWhere('name', 'like', $like)->orWhere('email', 'like', $like)
            ))

            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.comments.index', compact('comments', 'counts', 'status'));
    }

    public function approve(Comment $comment)
    {
        $comment->update(['status' => 'approved']);
        Activity::log('comment.approved', "Approved comment by {$comment->name}", $comment, ['post_id' => $comment->post_id]);

        return back()->with('success', 'Comment approved.');
    }

    public function spam(Comment $comment)
    {
        $comment->update(['status' => 'spam']);
        Activity::log('comment.spam', "Marked comment by {$comment->name} as spam", $comment, ['post_id' => $comment->post_id]);

        return back()->with('success', 'Marked as spam.');
    }

    public function destroy($id)
    {
        $comment = Comment::find($id);

        if ($comment) {
            Activity::log('comment.deleted', "Deleted comment by {$comment->name}", $comment, ['post_id' => $comment->post_id]);
            $comment->delete();
        } 

        return back()->with('success', 'Comment deleted.');
    }

        private function backToPost($post)
    {
        return redirect()->to('/post/' . $post->slug . '#comments');
    }
}
