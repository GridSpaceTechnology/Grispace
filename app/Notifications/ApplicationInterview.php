<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationInterview extends Notification implements ShouldQueue
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
            ->subject('Interview Invitation - '.$job->title)
            ->greeting("Hello {$notifiable->name}!")
            ->line("You have been invited to interview for the position of {$job->title}!")
            ->line('The employer has reviewed your application and would like to learn more about you.')
            ->action('View Interview Details', url('/candidate/jobs'))
            ->line('Thank you for using Gridspace!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_interview',
            'job_id' => $this->application->job_id,
            'job_title' => $this->application->job->title,
            'message' => "You have been invited to interview for {$this->application->job->title}",
        ];
    }
}
