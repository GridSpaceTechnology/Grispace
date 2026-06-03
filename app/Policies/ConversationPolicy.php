<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->employer_id === $user->id
            || $conversation->candidate_id === $user->id
            || $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['employer', 'candidate']);
    }

    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $conversation->employer_id === $user->id
            || $conversation->candidate_id === $user->id;
    }
}
