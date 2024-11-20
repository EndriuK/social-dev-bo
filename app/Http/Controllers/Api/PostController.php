<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')
            ->where('is_public', true)
            ->orWhere('user_id', Auth::id())
            ->latest()
            ->paginate(10);
            
        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'caption' => 'required|string|max:150',
            'image' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_public' => 'boolean',
        ]);

        $post = new Post($validated);
        $post->user_id = Auth::id();
        $post->date_posted = now();
        $post->save();

        return response()->json($post->load('user'), 201);
    }

    public function show(Post $post)
    {
        // Verifica che l'utente possa vedere questo post
        if (!$post->is_public && $post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($post->load('user'));
    }

    public function update(Request $request, Post $post)
    {
        // Verifica che l'utente sia il proprietario del post
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'caption' => 'sometimes|required|string|max:150',
            'image' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_public' => 'boolean',
        ]);

        $post->update($validated);

        return response()->json($post->load('user'));
    }

    public function destroy(Post $post)
    {
        // Verifica che l'utente sia il proprietario del post
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}