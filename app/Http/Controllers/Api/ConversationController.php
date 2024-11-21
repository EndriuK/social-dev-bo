<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\SearchHelper;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Conversation::where('user1_id', Auth::id())
            ->orWhere('user2_id', Auth::id())
            ->with(['user1', 'user2', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conversation) {
                // Determina l'altro utente nella conversazione
                $otherUser = $conversation->user1_id === Auth::id() 
                    ? $conversation->user2 
                    : $conversation->user1;
                
                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'image' => $otherUser->image
                    ],
                    'last_message' => $conversation->lastMessage,
                    'updated_at' => $conversation->last_message_at
                ];
            });

        return response()->json($conversations);
    }

    public function show(Conversation $conversation)
    {
        // Verifica che l'utente sia parte della conversazione
        if (!$this->userInConversation($conversation)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Non permettere conversazioni con se stessi
        if ($request->user_id === Auth::id()) {
            return response()->json(['message' => 'Cannot start conversation with yourself'], 400);
        }

        // Cerca una conversazione esistente
        $conversation = Conversation::where(function ($query) use ($request) {
            $query->where('user1_id', Auth::id())
                  ->where('user2_id', $request->user_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('user1_id', $request->user_id)
                  ->where('user2_id', Auth::id());
        })->first();

        // Se esiste, ritornala
        if ($conversation) {
            return response()->json($conversation->load(['user1', 'user2']));
        }

        // Altrimenti, crea una nuova conversazione
        $conversation = Conversation::create([
            'user1_id' => Auth::id(),
            'user2_id' => $request->user_id,
            'last_message_at' => now()
        ]);

        return response()->json($conversation->load(['user1', 'user2']), 201);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1'
        ]);

        $query = $request->input('query');
        $fuzzySearch = SearchHelper::getFuzzySearchCondition('messages.content', $query);
        $fuzzyNameSearch = SearchHelper::getFuzzySearchCondition('users.name', $query);

        $conversations = Conversation::where(function ($q) {
            $q->where('user1_id', Auth::id())
              ->orWhere('user2_id', Auth::id());
        })
        ->where(function ($q) use ($fuzzySearch, $fuzzyNameSearch) {
            // Cerca nei messaggi con fuzzy matching
            $q->whereHas('messages', function ($q) use ($fuzzySearch) {
                $q->whereRaw($fuzzySearch['condition'], $fuzzySearch['bindings']);
            })
            // Cerca nei nomi degli utenti con fuzzy matching
            ->orWhereHas('user1', function ($q) use ($fuzzyNameSearch) {
                $q->whereRaw($fuzzyNameSearch['condition'], $fuzzyNameSearch['bindings']);
            })
            ->orWhereHas('user2', function ($q) use ($fuzzyNameSearch) {
                $q->whereRaw($fuzzyNameSearch['condition'], $fuzzyNameSearch['bindings']);
            });
        })
        ->with(['user1', 'user2', 'lastMessage'])
        ->orderBy('last_message_at', 'desc')
        ->paginate(20)
        ->through(function ($conversation) use ($query, $fuzzySearch) {
            $otherUser = $conversation->user1_id === Auth::id() 
                ? $conversation->user2 
                : $conversation->user1;

            // Trova i messaggi corrispondenti usando fuzzy matching
            $matchingMessages = $conversation->messages()
                ->whereRaw($fuzzySearch['condition'], $fuzzySearch['bindings'])
                ->take(3)
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'content' => $message->content,
                        'created_at' => $message->created_at,
                        'sender_id' => $message->sender_id,
                        'relevance_score' => levenshtein(
                            strtolower($message->content),
                            strtolower($query)
                        )
                    ];
                })
                ->sortBy('relevance_score');

            return [
                'id' => $conversation->id,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'surname' => $otherUser->surname,
                    'image' => $otherUser->image
                ],
                'last_message' => $conversation->lastMessage,
                'updated_at' => $conversation->last_message_at,
                'matching_messages' => $matchingMessages,
                'relevance_score' => $matchingMessages->min('relevance_score') ?? PHP_INT_MAX
            ];
        })
        ->sortBy('relevance_score')
        ->values();

        return response()->json($conversations);
    }

    private function userInConversation(Conversation $conversation)
    {
        return $conversation->user1_id === Auth::id() || 
               $conversation->user2_id === Auth::id();
    }
}