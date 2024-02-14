<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\User;
use App\Models\Wpda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'wpda_id' => 'required|exists:wpdas,id',
            'comments_content' => 'required',
        ]);

        // Simpan komentar
        $comment = Comment::create([
            'user_id' => auth()->id(),
            'wpda_id' => $request->wpda_id,
            'comments_content' => $request->comments_content,
        ]);

        // Kirim notifikasi komentar
        $this->sendCommentNotification($request->wpda_id, auth()->user()->full_name);

        return response()->json(
            [
                'comment' => $comment,
            ],
        );
    }

    public function sendCommentNotification($wpdaId, $commenterName)
    {
        try {
            // Dapatkan pemilik WPDA
            $wpda = Wpda::findOrFail($wpdaId);
            $wpdaOwner = $wpda->user;

            // Dapatkan token perangkat pemilik WPDA
            $wpdaOwnerToken = $wpdaOwner->device_token;

            if (!$wpdaOwnerToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device token not found for WPDA owner',
                ], 404);
            }

            // Kirim notifikasi ke pemilik WPDA
            $client = new Client();
            $response = $client->post('https://onesignal.com/api/v1/notifications', [
                'headers' => [
                    'Authorization' => 'Basic OGRhZTY2M2YtMDNjOC00YTU2LTgyYzEtNzY4YzA2OWZiMDk0',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'app_id' => 'ae235573-b52c-44a5-b2c3-23d9de4232fa',
                    'include_player_ids' => [$wpdaOwnerToken],
                    'contents' => [
                        'en' => "$commenterName has commented on your WPDA",
                    ],
                ],
            ]);

            if ($response->getStatusCode() != 200) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send comment notification',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Comment notification sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending comment notification: ' . $e->getMessage(),
            ], 500);
        }
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
