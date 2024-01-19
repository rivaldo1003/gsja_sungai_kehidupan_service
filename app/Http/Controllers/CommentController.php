<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'wpda_id' => 'required|exists:wpdas,id',
            'comments_content' => 'required',
        ]);

        // Set zona waktu sesuai kebutuhan
        $commentTimestamp = now()->setTimezone('Asia/Jakarta');

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'wpda_id' => $request->wpda_id,
            'comments_content' => $request->comments_content,
            'created_at' => Carbon::now()->setTimezone('Asia/Jakarta'), // Sesuaikan dengan zona waktu Anda
            'updated_at' => Carbon::now()->setTimezone('Asia/Jakarta'), // Sesuaikan dengan zona waktu Anda
        ]);

        // Load informasi pengguna (commentator) dalam respons
        $comment->load('user:id,full_name,email');

        return response()->json(['comment' => $comment]);
    }


    public function deleteComment($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
            ], 404);
        }

        if ($comment->user_id !== Auth::user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this comment',
            ], 403);
        }

        $comment->delete();



        return response()->json([
            'success' => true,
            'message' => 'Comment has been deleted successfully',
            'data' => new CommentResource($comment),
        ]);
    }
}
