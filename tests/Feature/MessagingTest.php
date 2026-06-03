<?php

use App\Models\Conversation;
use App\Models\Job;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use App\Policies\ConversationPolicy;
use Illuminate\Database\QueryException;

beforeEach(function () {
    $this->employer = User::factory()->create(['role' => 'employer']);
    $this->candidate = User::factory()->create(['role' => 'candidate']);
});

it('creates a conversation between employer and candidate', function () {
    $conversation = Conversation::create([
        'employer_id' => $this->employer->id,
        'candidate_id' => $this->candidate->id,
        'last_message_at' => now(),
    ]);

    expect($conversation)->toBeInstanceOf(Conversation::class)
        ->and($conversation->employer_id)->toBe($this->employer->id)
        ->and($conversation->candidate_id)->toBe($this->candidate->id);
});

it('prevents duplicate conversations for same employer-candidate-job', function () {
    $job = Job::create([
        'employer_id' => $this->employer->id,
        'title' => 'Software Engineer',
        'slug' => 'software-engineer',
        'role' => 'Engineering',
        'employment_type' => 'full_time',
        'work_preference' => 'remote',
        'status' => 'open',
    ]);

    Conversation::create([
        'employer_id' => $this->employer->id,
        'candidate_id' => $this->candidate->id,
        'job_id' => $job->id,
        'last_message_at' => now(),
    ]);

    expect(function () use ($job) {
        Conversation::create([
            'employer_id' => $this->employer->id,
            'candidate_id' => $this->candidate->id,
            'job_id' => $job->id,
            'last_message_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});

it('can send a message in a conversation', function () {
    $conversation = Conversation::create([
        'employer_id' => $this->employer->id,
        'candidate_id' => $this->candidate->id,
        'last_message_at' => now(),
    ]);

    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $this->employer->id,
        'sender_type' => 'employer',
        'message' => 'Hello, we would like to invite you for an interview.',
        'is_read' => false,
    ]);

    expect($message)->toBeInstanceOf(Message::class)
        ->and($message->message)->toBe('Hello, we would like to invite you for an interview.')
        ->and($message->sender_type)->toBe('employer');
});

it('identifies the other participant in a conversation', function () {
    $conversation = Conversation::create([
        'employer_id' => $this->employer->id,
        'candidate_id' => $this->candidate->id,
        'last_message_at' => now(),
    ]);

    $other = $conversation->otherParticipant($this->employer);
    expect($other->id)->toBe($this->candidate->id);
});

it('tracks unread message count', function () {
    $conversation = Conversation::create([
        'employer_id' => $this->employer->id,
        'candidate_id' => $this->candidate->id,
        'last_message_at' => now(),
    ]);

    Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $this->employer->id,
        'sender_type' => 'employer',
        'message' => 'Interview invitation',
        'is_read' => false,
    ]);

    expect($conversation->unreadMessagesCount($this->candidate))->toBe(1)
        ->and($conversation->unreadMessagesCount($this->employer))->toBe(0);
});

it('allows employer to access their conversations', function () {
    $conversation = Conversation::create([
        'employer_id' => $this->employer->id,
        'candidate_id' => $this->candidate->id,
        'last_message_at' => now(),
    ]);

    $this->actingAs($this->employer)
        ->get(route('employer.messages'))
        ->assertOk();

    $this->actingAs($this->employer)
        ->get(route('employer.messages.show', $conversation))
        ->assertOk();
});

it('prevents unauthorized access to conversations', function () {
    $otherEmployer = User::factory()->create(['role' => 'employer']);

    $conversation = Conversation::create([
        'employer_id' => $this->employer->id,
        'candidate_id' => $this->candidate->id,
        'last_message_at' => now(),
    ]);

    $this->actingAs($otherEmployer)
        ->get(route('employer.messages.show', $conversation))
        ->assertForbidden();
});

it('can create and read message reads', function () {
    $conversation = Conversation::create([
        'employer_id' => $this->employer->id,
        'candidate_id' => $this->candidate->id,
        'last_message_at' => now(),
    ]);

    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $this->candidate->id,
        'sender_type' => 'candidate',
        'message' => 'I am interested in this position.',
        'is_read' => false,
    ]);

    $read = MessageRead::create([
        'message_id' => $message->id,
        'user_id' => $this->employer->id,
        'read_at' => now(),
    ]);

    expect($read)->toBeInstanceOf(MessageRead::class)
        ->and($message->reads()->count())->toBe(1);
});

it('scopes conversations for a specific user', function () {
    Conversation::create([
        'employer_id' => $this->employer->id,
        'candidate_id' => $this->candidate->id,
        'last_message_at' => now(),
    ]);

    $conversations = Conversation::forUser($this->employer)->get();
    expect($conversations)->toHaveCount(1);
});

it('attachments are optional on messages', function () {
    $conversation = Conversation::create([
        'employer_id' => $this->employer->id,
        'candidate_id' => $this->candidate->id,
        'last_message_at' => now(),
    ]);

    $message = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $this->employer->id,
        'sender_type' => 'employer',
        'message' => 'Please find the attached document.',
        'is_read' => false,
    ]);

    expect($message->attachment_path)->toBeNull()
        ->and($message->attachment_type)->toBeNull();
});

it('prevents candidates from seeing employer-only conversations via policy', function () {
    $conversation = Conversation::create([
        'employer_id' => $this->employer->id,
        'candidate_id' => $this->candidate->id,
        'last_message_at' => now(),
    ]);

    $policy = new ConversationPolicy;

    expect($policy->view($this->employer, $conversation))->toBeTrue()
        ->and($policy->view($this->candidate, $conversation))->toBeTrue()
        ->and($policy->sendMessage($this->employer, $conversation))->toBeTrue()
        ->and($policy->sendMessage($this->candidate, $conversation))->toBeTrue();
});

it('has the correct sender_type on messages', function () {
    $conversation = Conversation::create([
        'employer_id' => $this->employer->id,
        'candidate_id' => $this->candidate->id,
        'last_message_at' => now(),
    ]);

    $employerMsg = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $this->employer->id,
        'sender_type' => 'employer',
        'message' => 'From employer',
        'is_read' => false,
    ]);

    $candidateMsg = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $this->candidate->id,
        'sender_type' => 'candidate',
        'message' => 'From candidate',
        'is_read' => false,
    ]);

    expect($employerMsg->sender_type)->toBe('employer')
        ->and($candidateMsg->sender_type)->toBe('candidate');
});
