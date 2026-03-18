<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationShortlisted extends Notification implements ShouldQueue
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
            ->subject('You have been shortlisted!')
            ->greeting("Hello {$notifiable->name}!")
            ->line("Great news! You have been shortlisted for the position of {$job->title}.")
            ->line('The employer liked your profile and would like to move forward with your application.')
            ->action('View Job Details', url('/candidate/jobs'))
            ->line('Thank you for using Gridspace!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_shortlisted',
            'job_id' => $this->application->job_id,
            'job_title' => $this->application->job->title,
            'message' => "You have been shortlisted for {$this->application->job->title}",
        ];
    }
}
