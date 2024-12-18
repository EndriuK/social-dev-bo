<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\CreatesNotifications;

class LikeController extends Controller
{
    use CreatesNotifications;

    public function toggle(Post $post)
    {
        $like = $post->likes()->where('user_id', Auth::id())->first();

        if ($like) {
            $like->delete();
            $post->decrement('likes_count');
            return response()->json([
                'liked' => false, 
                'likes_count' => $post->likes_count
            ]);
        }

        $post->likes()->create(['user_id' => Auth::id()]);
        $post->increment('likes_count');
        
        // Crea notifica per il proprietario del post
        $this->createNotification(
            $post->user_id,
            'like',
            [
                'post_id' => $post->id,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name
            ]
        );

        return response()->json([
            'liked' => true, 
            'likes_count' => $post->likes_count
        ]);
    }

    public function isLiked(Post $post)
    {
        $liked = $post->likes()->where('user_id', Auth::id())->exists();
        return response()->json(['liked' => $liked]);
    }
}