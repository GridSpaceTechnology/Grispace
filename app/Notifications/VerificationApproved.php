<?php

namespace App\Notifications;

use App\Models\CandidateVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationApproved extends Notification
{
    use Queueable;

    public function __construct(public CandidateVerification $verification) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verification Approved')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Great news! Your '.$this->verification->verificationType->name.' has been approved.')
            ->line('Your trust score has been updated. You are now more visible to employers.')
            ->action('View Verification', url('/candidate/verification'))
            ->line('Thank you for being a trusted member of Gridspace!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Verification Approved',
            'message' => 'Your '.$this->verification->verificationType->name.' has been approved!',
            'verification_id' => $this->verification->id,
            'type' => 'verification_approved',
        ];
    }
}
