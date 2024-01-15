<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'wpda_id' => 'required|exists:wpdas,id',
            'comments_content' => 'required',
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'wpda_id' => $request->wpda_id,
            'comments_content' => $request->comments_content,
        ]);

        // Load informasi pengguna (commentator) dalam respons
        $comment->load('user');

        return response()->json(['comment' => $comment]);
    }
}
