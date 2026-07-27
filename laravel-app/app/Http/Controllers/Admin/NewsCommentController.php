<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsComment;
use Illuminate\Http\Request;

class NewsCommentController extends Controller
{
    public function index()
    {
        $comments = NewsComment::with('news')->latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $comments]);
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
