<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\CreatesNotifications;
use App\Helpers\SearchHelper;

class MessageController extends Controller
{
    use CreatesNotifications;

    public function store(Request $request, Conversation $conversation)
    {
        // Verifica che l'utente sia parte della conversazione
        if (!$this->userInConversation($conversation)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'content' => $request->content
        ]);

        // Aggiorna il timestamp dell'ultimo messaggio
        $conversation->update(['last_message_at' => now()]);

        // Determina il destinatario per la notifica
        $recipientId = $conversation->user1_id === Auth::id() 
            ? $conversation->user2_id 
            : $conversation->user1_id;

        // Crea una notifica per il destinatario
        $this->createNotification(
            $recipientId,
            'message',
            [
                'conversation_id' => $conversation->id,
                'sender_id' => Auth::id(),
                'sender_name' => Auth::user()->name,
                'message_preview' => substr($request->content, 0, 50)
            ]
        );

        return response()->json($message->load('sender'), 201);
    }

    public function markAsRead(Conversation $conversation)
    {
        if (!$this->userInConversation($conversation)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Marca come letti tutti i messaggi non letti inviati dall'altro utente
        $conversation->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json(['message' => 'Messages marked as read']);
    }

    private function userInConversation(Conversation $conversation)
    {
        return $conversation->user1_id === Auth::id() || 
               $conversation->user2_id === Auth::id();
    }

    public function search(Request $request, Conversation $conversation)
    {
        if (!$this->userInConversation($conversation)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'query' => 'required|string|min:1'
        ]);

        $query = $request->input('query');
        $fuzzySearch = SearchHelper::getFuzzySearchCondition('content', $query);

        $messages = $conversation->messages()
            ->whereRaw($fuzzySearch['condition'], $fuzzySearch['bindings'])
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($message) use ($query) {
                $relevanceScore = levenshtein(
                    strtolower($message->content),
                    strtolower($query)
                );

                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->created_at,
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                        'image' => $message->sender->image
                    ],
                    'relevance_score' => $relevanceScore
                ];
            });

        // Ordina i risultati per rilevanza
        $messages->getCollection()->sortBy('relevance_score');

        return response()->json($messages);
    }
}