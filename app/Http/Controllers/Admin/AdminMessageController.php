<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMessageController extends Controller
{
    public function index(Request $request): View
    {
        $query = Conversation::with(['employer', 'candidate', 'job', 'latestMessage'])
            ->orderBy('last_message_at', 'desc');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->whereHas('employer', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('candidate', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('job', fn ($q) => $q->where('title', 'like', "%{$term}%"));
            });
        }

        $conversations = $query->paginate(20);

        return view('admin.messages.index', compact('conversations'));
    }

    public function show(Conversation $conversation): View
    {
        $conversation->load(['employer', 'candidate', 'candidate.candidateProfile', 'job']);
        $messages = $conversation->messages()
            ->with(['sender', 'reads.user'])
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return view('admin.messages.show', compact('conversation', 'messages'));
    }

    public function stats(): View
    {
        $totalConversations = Conversation::count();
        $totalMessages = Message::count();
        $messagesToday = Message::whereDate('created_at', today())->count();
        $conversationsToday = Conversation::whereDate('created_at', today())->count();
        $avgMessagesPerConversation = $totalConversations > 0
            ? round($totalMessages / $totalConversations, 1)
            : 0;
        $topConversations = Conversation::withCount('messages')
            ->orderBy('messages_count', 'desc')
            ->take(10)
            ->get();
        $latestMessages = Message::with(['sender', 'conversation'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('admin.messages.stats', compact(
            'totalConversations',
            'totalMessages',
            'messagesToday',
            'conversationsToday',
            'avgMessagesPerConversation',
            'topConversations',
            'latestMessages',
        ));
    }

    public function destroy(Conversation $conversation)
    {
        $conversation->delete();

        return redirect()->route('admin.messages.index')
            ->with('success', 'Conversation deleted successfully.');
    }
}
