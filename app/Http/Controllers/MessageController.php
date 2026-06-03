<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\TypingIndicator;
use App\Models\Conversation;
use App\Models\EmployerShortlist;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\Message;
use App\Models\MessageRead as MessageReadModel;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessageController extends Controller
{
    private const ALLOWED_ATTACHMENT_TYPES = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    private const MAX_ATTACHMENT_SIZE = 10240;

    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();

        $conversations = Conversation::forUser($user)
            ->with(['employer', 'candidate', 'candidate.candidateProfile', 'job', 'latestMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($conversations);
        }

        return view('messages.index', [
            'conversations' => $conversations,
            'selectedConversation' => null,
        ]);
    }

    public function show(Request $request, Conversation $conversation): View|JsonResponse
    {
        $user = Auth::user();

        if (! $this->canAccess($user, $conversation)) {
            abort(403);
        }

        $conversation->load(['employer', 'candidate', 'candidate.candidateProfile', 'job']);

        $messages = $conversation->messages()
            ->with(['sender', 'reads'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $this->markMessagesAsRead($conversation, $user);

        $conversations = Conversation::forUser($user)
            ->with(['employer', 'candidate', 'candidate.candidateProfile', 'job', 'latestMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return view('messages.show', [
            'conversation' => $conversation,
            'conversations' => $conversations,
            'selectedConversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function getMessages(Request $request, Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if (! $this->canAccess($user, $conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with(['sender', 'reads.user'])
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        $this->markMessagesAsRead($conversation, $user);

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if (! $this->canAccess($user, $conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required_without:attachment|string|max:10000',
            'attachment' => 'nullable|file|max:'.self::MAX_ATTACHMENT_SIZE,
        ]);

        $attachmentPath = null;
        $attachmentType = null;
        $attachmentName = null;
        $attachmentSize = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            if (! in_array($file->getMimeType(), self::ALLOWED_ATTACHMENT_TYPES)) {
                return response()->json(['error' => 'Invalid file type.'], 422);
            }

            $attachmentPath = $file->store('attachments/'.$conversation->id, 'public');
            $attachmentType = $file->getMimeType();
            $attachmentName = $file->getClientOriginalName();
            $attachmentSize = $file->getSize();
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'sender_type' => $user->role,
            'message' => $request->message ?? '',
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
            'attachment_name' => $attachmentName,
            'attachment_size' => $attachmentSize,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        $message->load('sender');

        broadcast(new MessageSent($message))->toOthers();

        $recipient = $user->id === $conversation->employer_id
            ? $conversation->candidate
            : $conversation->employer;

        $recipient->notify(new NewMessageNotification($message, $user));

        return response()->json([
            'message' => $message->load('reads'),
        ]);
    }

    public function createOrGetConversation(Request $request, User $candidate): JsonResponse
    {
        $user = Auth::user();

        if ($user->isCandidate()) {
            $hasConnection = JobApplication::where('candidate_id', $user->id)
                ->whereHas('job', fn ($q) => $q->where('employer_id', $candidate->id))
                ->exists();

            if (! $hasConnection) {
                return response()->json(['error' => 'You can only message employers you have applied to.'], 403);
            }
        } else {
            $hasConnection = EmployerShortlist::where('employer_id', $user->id)
                ->where('candidate_id', $candidate->id)
                ->exists()
                || Interview::where('employer_id', $user->id)
                    ->where('candidate_id', $candidate->id)
                    ->exists()
                || JobApplication::whereHas('job', fn ($q) => $q->where('employer_id', $user->id))
                    ->where('candidate_id', $candidate->id)
                    ->exists();

            if (! $hasConnection) {
                return response()->json(['error' => 'You can only message candidates with a connection.'], 403);
            }
        }

        $jobId = $request->get('job_id');

        $conversation = Conversation::where('employer_id', $user->isEmployer() ? $user->id : $candidate->id)
            ->where('candidate_id', $user->isCandidate() ? $user->id : $candidate->id)
            ->when($jobId, fn ($q) => $q->where('job_id', $jobId))
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'employer_id' => $user->isEmployer() ? $user->id : $candidate->id,
                'candidate_id' => $user->isCandidate() ? $user->id : $candidate->id,
                'job_id' => $jobId,
                'last_message_at' => now(),
            ]);
        }

        return response()->json([
            'conversation_id' => $conversation->id,
        ]);
    }

    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if (! $this->canAccess($user, $conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->markMessagesAsRead($conversation, $user);

        return response()->json(['success' => true]);
    }

    public function typing(Request $request, Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if (! $this->canAccess($user, $conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate(['typing' => 'required|boolean']);

        broadcast(new TypingIndicator(
            conversationId: $conversation->id,
            userId: $user->id,
            userName: $user->name,
            typing: $request->boolean('typing'),
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $term = $request->get('q');

        $conversations = Conversation::forUser($user)
            ->with(['employer', 'candidate', 'candidate.candidateProfile', 'job', 'latestMessage'])
            ->where(function ($query) use ($term) {
                $query->whereHas('employer', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('candidate', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('job', fn ($q) => $q->where('title', 'like', "%{$term}%"))
                    ->orWhereHas('messages', fn ($q) => $q->where('message', 'like', "%{$term}%"));
            })
            ->orderBy('last_message_at', 'desc')
            ->get();

        return response()->json($conversations);
    }

    private function canAccess(User $user, Conversation $conversation): bool
    {
        return $conversation->employer_id === $user->id || $conversation->candidate_id === $user->id;
    }

    private function markMessagesAsRead(Conversation $conversation, User $user): void
    {
        $unreadMessages = $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->get();

        foreach ($unreadMessages as $message) {
            $message->update(['is_read' => true]);

            MessageReadModel::create([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'read_at' => now(),
            ]);

            broadcast(new MessageRead(
                conversationId: $conversation->id,
                messageId: $message->id,
                userId: $user->id,
                readAt: now()->toIso8601String(),
            ))->toOthers();
        }
    }
}
