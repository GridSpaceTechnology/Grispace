<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\EmployerShortlist;
use App\Models\JobApplication;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Conversation::where(function ($q) use ($user) {
            $q->where('employer_id', $user->id)
                ->orWhere('candidate_id', $user->id);
        })->with(['employer', 'candidate', 'job', 'latestMessage']);

        if ($user->isEmployer()) {
            $conversations = $query->orderBy('last_message_at', 'desc')->get();
        } else {
            $conversations = $query->orderBy('last_message_at', 'desc')->get();
        }

        return view('messages.index', [
            'conversations' => $conversations,
        ]);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $user = Auth::user();

        if (! $this->canAccessConversation($user, $conversation)) {
            abort(403, 'You cannot access this conversation.');
        }

        $conversation->load(['employer', 'candidate', 'job', 'messages' => function ($q) {
            $q->orderBy('created_at', 'desc')
                ->paginate(50);
        }]);

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('messages.show', [
            'conversation' => $conversation,
        ]);
    }

    public function getMessages(Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if (! $this->canAccessConversation($user, $conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if (! $this->canAccessConversation($user, $conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->message,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        $message->load('sender:id,name');

        event(new MessageSent($message));

        $recipient = $user->id === $conversation->employer_id
            ? $conversation->candidate
            : $conversation->employer;

        $recipient->notify(new NewMessageNotification($message, $user));

        return response()->json([
            'message' => $message,
        ]);
    }

    public function createOrGetConversation(Request $request, User $candidate): JsonResponse
    {
        $user = Auth::user();

        if ($user->isCandidate()) {
            $hasApplied = JobApplication::where('candidate_id', $user->id)
                ->whereHas('job', fn ($q) => $q->where('employer_id', $candidate->id))
                ->exists();

            if (! $hasApplied) {
                return response()->json(['error' => 'You can only message employers you have applied to.'], 403);
            }
        } else {
            $hasConnection = EmployerShortlist::where('employer_id', $user->id)
                ->where('candidate_id', $candidate->id)
                ->exists();

            $hasInterview = \App\Models\Interview::where('employer_id', $user->id)
                ->where('candidate_id', $candidate->id)
                ->exists();

            $hasApplication = JobApplication::whereHas('job', fn ($q) => $q->where('employer_id', $user->id))
                ->where('candidate_id', $candidate->id)
                ->exists();

            if (! $hasConnection && ! $hasInterview && ! $hasApplication) {
                return response()->json(['error' => 'You can only message candidates you have shortlisted, interviewed, or have applications from.'], 403);
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

        if (! $this->canAccessConversation($user, $conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    protected function canAccessConversation(User $user, Conversation $conversation): bool
    {
        return $conversation->employer_id === $user->id || $conversation->candidate_id === $user->id;
    }
}
