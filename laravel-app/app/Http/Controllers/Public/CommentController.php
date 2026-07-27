<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewsComment;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $newsId = $request->query('news_id');
        $comments = NewsComment::where('news_id', $newsId)
            ->where('status', 'approved')
            ->latest()
            ->get();
            
        return response()->json($comments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'news_id' => 'required|integer',
            'name' => 'required|string|max:100',
            'content' => 'required|string',
        ]);
        
        $comment = NewsComment::create([
            'news_id' => $request->news_id,
            'name' => $request->name,
            'content' => $request->content,
            'status' => 'pending',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Comment submitted and awaiting approval.'
        ]);
    }

    public function vote(Request $request, $id)
    {
        $comment = NewsComment::find($id);
        
        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found.'
            ], 404);
        }
        
        $type = $request->input('type');
        
        if ($type === 'upvote') {
            $comment->increment('upvotes');
        } elseif ($type === 'downvote') {
            $comment->increment('downvotes');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Vote recorded.'
        ]);
    }
}
