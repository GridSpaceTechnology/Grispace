<?php

namespace App\Notifications;

use App\Models\CandidateVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationInfoRequested extends Notification
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
            ->subject('Additional Information Required')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('We need additional information for your '.$this->verification->verificationType->name.'.')
            ->line('Notes: '.($this->verification->notes ?? 'Please upload clearer documents.'))
            ->action('Upload Documents', url('/candidate/verification'))
            ->line('Thank you for your cooperation!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Additional Information Required',
            'message' => 'We need more information for your '.$this->verification->verificationType->name.'.',
            'verification_id' => $this->verification->id,
            'type' => 'verification_info_requested',
        ];
    }
}
