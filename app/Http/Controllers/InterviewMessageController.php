<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Interview;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterviewMessageController extends Controller
{
    public function sendInvitation(Request $request, Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if (! $user->isEmployer()) {
            return response()->json(['error' => 'Only employers can send interview invitations.'], 403);
        }

        $request->validate([
            'interview_type' => 'required|in:phone,video,onsite,technical,behavioral,panel',
            'scheduled_at' => 'required|date|after:now',
            'meeting_link' => 'nullable|url',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $candidate = $conversation->candidate;

        $interview = Interview::create([
            'employer_id' => $user->id,
            'candidate_id' => $candidate->id,
            'job_id' => $conversation->job_id,
            'interview_type' => $request->interview_type,
            'scheduled_at' => $request->scheduled_at,
            'meeting_link' => $request->meeting_link,
            'location' => $request->location,
            'status' => Interview::STATUS_SCHEDULED,
            'notes' => $request->notes,
        ]);

        $messageContent = "📅 Interview Invitation\n\n";
        $messageContent .= 'Type: '.ucfirst($request->interview_type)."\n";
        $messageContent .= 'Date: '.Carbon::parse($request->scheduled_at)->format('l, F j, Y g:i A')."\n";
        if ($request->meeting_link) {
            $messageContent .= 'Meeting Link: '.$request->meeting_link."\n";
        }
        if ($request->location) {
            $messageContent .= 'Location: '.$request->location."\n";
        }
        if ($request->notes) {
            $messageContent .= "\nNotes: ".$request->notes;
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'sender_type' => 'employer',
            'message' => $messageContent,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'message' => $message->load('sender'),
            'interview_id' => $interview->id,
        ]);
    }

    public function respondToInvitation(Request $request, Interview $interview): JsonResponse
    {
        $user = Auth::user();

        if ($interview->candidate_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $request->validate(['action' => 'required|in:accepted,declined']);

        $conversation = Conversation::where('employer_id', $interview->employer_id)
            ->where('candidate_id', $user->id)
            ->where('job_id', $interview->job_id)
            ->first();

        if (! $conversation) {
            return response()->json(['error' => 'Conversation not found.'], 404);
        }

        if ($request->action === 'accepted') {
            $interview->update(['status' => 'scheduled']);
            $responseText = '✅ I have accepted the interview invitation for '.
                $interview->scheduled_at->format('l, F j, Y g:i A').'.';
        } else {
            $interview->update(['status' => Interview::STATUS_CANCELLED]);
            $responseText = '❌ I have declined the interview invitation for '.
                $interview->scheduled_at->format('l, F j, Y g:i A').'.';
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'sender_type' => 'candidate',
            'message' => $responseText,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'message' => $message->load('sender'),
            'interview_status' => $interview->status,
        ]);
    }

    public function sendDecision(Request $request, Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if (! $user->isEmployer()) {
            return response()->json(['error' => 'Only employers can send hiring decisions.'], 403);
        }

        $request->validate([
            'decision' => 'required|in:hired,rejected,on_hold',
            'notes' => 'nullable|string|max:2000',
        ]);

        $decisionLabels = [
            'hired' => '🎉 Hiring Decision: Accepted',
            'rejected' => '💼 Hiring Decision: Not Selected',
            'on_hold' => '⏳ Hiring Decision: On Hold',
        ];

        $messageContent = $decisionLabels[$request->decision]."\n\n";
        if ($request->notes) {
            $messageContent .= $request->notes;
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'sender_type' => 'employer',
            'message' => $messageContent,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        if ($application = $conversation->candidate->jobApplications()
            ->whereHas('job', fn ($q) => $q->where('employer_id', $user->id))
            ->latest()
            ->first()
        ) {
            $statusMap = [
                'hired' => 'hired',
                'rejected' => 'rejected',
                'on_hold' => 'under_review',
            ];
            $application->update(['status' => $statusMap[$request->decision]]);
        }

        return response()->json([
            'message' => $message->load('sender'),
        ]);
    }
}
