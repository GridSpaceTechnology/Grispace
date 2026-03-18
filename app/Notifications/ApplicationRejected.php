<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationRejected extends Notification implements ShouldQueue
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
            ->subject('Application Update - '.$job->title)
            ->greeting("Hello {$notifiable->name},")
            ->line("Thank you for your interest in the position of {$job->title}.")
            ->line('After careful consideration, we have decided to move forward with other candidates at this time.')
            ->line('We encourage you to apply for other positions that match your skills.')
            ->action('Browse More Jobs', url('/candidate/jobs'))
            ->line('Thank you for using Gridspace!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_rejected',
            'job_id' => $this->application->job_id,
            'job_title' => $this->application->job->title,
            'message' => "Your application for {$this->application->job->title} was not selected",
        ];
    }
}
