<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wpda;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function likeWpda($userId, $wpdaId)
    {
        $user = User::findOrFail($userId);
        $wpda = Wpda::findOrFail($wpdaId);

        $user->likes()->attach($wpda);
        $wpda->incrementTotalLikes();

        return response()->json(['message' => 'WPDA liked successfully']);
    }

    public function unlikeWpda($userId, $wpdaId)
    {
        $user = User::findOrFail($userId);
        $wpda = Wpda::findOrFail($wpdaId);

        $user->likes()->detach($wpda);
        $wpda->decrementTotalLikes();

        return response()->json(['message' => 'WPDA unliked successfully']);
    }
}
