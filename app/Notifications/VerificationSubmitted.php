<?php

namespace App\Notifications;

use App\Models\CandidateVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationSubmitted extends Notification
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
            ->subject('Verification Submitted')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your '.$this->verification->verificationType->name.' has been submitted successfully.')
            ->line('Our team will review your documents and update you on the status.')
            ->action('View Verification', url('/candidate/verification'))
            ->line('Thank you for helping us keep Gridspace trustworthy!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Verification Submitted',
            'message' => 'Your '.$this->verification->verificationType->name.' has been submitted for review.',
            'verification_id' => $this->verification->id,
            'type' => 'verification_submitted',
        ];
    }
}
