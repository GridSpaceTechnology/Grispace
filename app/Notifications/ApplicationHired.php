<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationHired extends Notification implements ShouldQueue
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
            ->subject('Congratulations! You Have Been Hired!')
            ->greeting("Congratulations {$notifiable->name}!")
            ->line("You have been hired for the position of {$job->title}!")
            ->line('Welcome aboard! We wish you all the best in your new role.')
            ->action('View Details', url('/candidate/jobs'))
            ->line('Thank you for using Gridspace!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_hired',
            'job_id' => $this->application->job_id,
            'job_title' => $this->application->job->title,
            'message' => "You have been hired for {$this->application->job->title}!",
        ];
    }
}
