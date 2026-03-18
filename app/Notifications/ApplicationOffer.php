<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationOffer extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public JobApplication $application) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $job = $this->application->job;

        return (new MailMessage)
            ->subject('Job Offer Received - '.$job->title)
            ->greeting("Congratulations {$notifiable->name}!")
            ->line("You have received a job offer for the position of {$job->title}!")
            ->line('The employer is excited to extend this offer to you.')
            ->action('View Offer Details', url('/candidate/jobs'))
            ->line('Thank you for using Gridspace!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_offer',
            'job_id' => $this->application->job_id,
            'job_title' => $this->application->job->title,
            'message' => "You received a job offer for {$this->application->job->title}",
        ];
    }
}
