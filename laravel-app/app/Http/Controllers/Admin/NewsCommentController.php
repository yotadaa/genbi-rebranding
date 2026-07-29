<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsComment;
use Illuminate\Http\Request;

class NewsCommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = NewsComment::with('news')->latest('created_at')->paginate(min(100, max(1, (int) $request->query('per_page', 50))));
        $data = $comments->getCollection()->map(fn (NewsComment $comment) => [
            'id' => $comment->comment_id,
            'comment_id' => $comment->comment_id,
            'article' => $comment->news?->news_title ?? 'Berita dihapus',
            'news_id' => $comment->news_id,
            'name' => $comment->name,
            'email' => $comment->email,
            'text' => $comment->content,
            'comment' => $comment->content,
            'status' => ucfirst((string) $comment->status),
            'date' => optional($comment->created_at)->format('Y-m-d') ?? (string) $comment->created_at,
            'created_at' => $comment->created_at,
        ])->values();
        return response()->json(['success' => true, 'data' => $data, 'meta' => ['page' => $comments->currentPage(), 'total' => $comments->total(), 'per_page' => $comments->perPage()]]);
    }

    public function approve($id)
    {
        $comment = NewsComment::findOrFail($id);
        $comment->update(['status' => 'approved']);
        return response()->json(['success' => true, 'message' => 'Comment approved.']);
    }

    public function reject($id)
    {
        $comment = NewsComment::findOrFail($id);
        $comment->update(['status' => 'rejected']);
        return response()->json(['success' => true, 'message' => 'Comment rejected.']);
    }

    public function destroy($id)
    {
        $comment = NewsComment::findOrFail($id);
        $comment->delete();
        return response()->json(['success' => true, 'message' => 'Comment deleted.']);
    }
}
