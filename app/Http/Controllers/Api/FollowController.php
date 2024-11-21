<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggle(User $user)
    {
        // Non puoi seguire te stesso
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Cannot follow yourself'], 400);
        }

        $follow = Follow::where('follower_id', Auth::id())
                       ->where('following_id', $user->id)
                       ->first();

        if ($follow) {
            $follow->delete();
            return response()->json(['status' => 'unfollowed']);
        }

        // Se il profilo è privato, la richiesta va in pending
        $status = $user->is_private ? 'pending' : 'accepted';

        Follow::create([
            'follower_id' => Auth::id(),
            'following_id' => $user->id,
            'status' => $status
        ]);

        return response()->json([
            'status' => $status,
            'message' => $user->is_private ? 'Follow request sent' : 'Following'
        ]);
    }

    public function acceptRequest(Follow $follow)
    {
        // Verifica che la richiesta sia diretta all'utente autenticato
        if ($follow->following_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $follow->update(['status' => 'accepted']);

        return response()->json(['message' => 'Follow request accepted']);
    }

    public function rejectRequest(Follow $follow)
    {
        if ($follow->following_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $follow->delete();

        return response()->json(['message' => 'Follow request rejected']);
    }

    public function getPendingRequests()
    {
        $requests = Follow::with('follower')
            ->where('following_id', Auth::id())
            ->where('status', 'pending')
            ->get();

        return response()->json($requests);
    }

    public function followers(User $user)
    {
        $followers = Follow::with('follower')
            ->where('following_id', $user->id)
            ->where('status', 'accepted')
            ->get();

        return response()->json($followers);
    }

    public function following(User $user)
    {
        $following = Follow::with('following')
            ->where('follower_id', $user->id)
            ->where('status', 'accepted')
            ->get();

        return response()->json($following);
    }
} 