<?php

namespace App\Notifications;

use App\Models\CandidateVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationRejected extends Notification
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
            ->subject('Verification Update')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your '.$this->verification->verificationType->name.' could not be approved at this time.')
            ->line('Reason: '.($this->verification->notes ?? 'Please review and resubmit with correct documents.'))
            ->action('View Verification', url('/candidate/verification'))
            ->line('If you have questions, please contact our support team.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Verification Update',
            'message' => 'Your '.$this->verification->verificationType->name.' could not be approved. Please check the notes and resubmit.',
            'verification_id' => $this->verification->id,
            'type' => 'verification_rejected',
        ];
    }
}
