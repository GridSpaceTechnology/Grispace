<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(public Message $message, public User $sender) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New message from '.$this->sender->name)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('You received a new message from '.$this->sender->name.'.')
            ->line('"'.\Str::limit($this->message->message, 100).'"')
            ->action('View Message', url('/messages/'.$this->message->conversation_id))
            ->line('Thank you for using Gridspace!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New message from '.$this->sender->name,
            'message' => \Str::limit($this->message->message, 100),
            'sender_id' => $this->sender->id,
            'conversation_id' => $this->message->conversation_id,
        ];
    }
}
