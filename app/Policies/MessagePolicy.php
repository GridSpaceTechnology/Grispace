<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function view(User $user, Message $message): bool
    {
        return $message->conversation->employer_id === $user->id
            || $message->conversation->candidate_id === $user->id
            || $user->role === 'admin';
    }
}
