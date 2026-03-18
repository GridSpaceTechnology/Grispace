<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewScheduled extends Notification
{
    use Queueable;

    public function __construct(public Interview $interview) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $job = $this->interview->job;
        $employer = $this->interview->employer;

        return (new MailMessage)
            ->subject('Interview Scheduled - '.($job?->title ?? 'Job Application'))
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('You have been invited to an interview.')
            ->line('**Interview Details:**')
            ->line('- **Date:** '.$this->interview->scheduled_at->format('F j, Y'))
            ->line('- **Time:** '.$this->interview->scheduled_at->format('g:i A'))
            ->line('- **Type:** '.ucfirst($this->interview->interview_type))
            ->when($this->interview->meeting_link, fn ($m) => $m->line('- **Meeting Link:** '.$this->interview->meeting_link))
            ->when($this->interview->location, fn ($m) => $m->line('- **Location:** '.$this->interview->location))
            ->when($this->interview->notes, fn ($m) => $m->line('- **Notes:** '.$this->interview->notes))
            ->line('Company: '.($employer?->employerProfile?->company_name ?? $employer?->name ?? 'Employer'))
            ->when($this->interview->meeting_link, fn ($m) => $m->action('Join Interview', $this->interview->meeting_link));
    }

    public function toArray(object $notifiable): array
    {
        $job = $this->interview->job;

        return [
            'title' => 'Interview Scheduled',
            'message' => 'You have been invited to an interview for '.($job?->title ?? 'a job'),
            'interview_id' => $this->interview->id,
            'scheduled_at' => $this->interview->scheduled_at->toIso8601String(),
            'interview_type' => $this->interview->interview_type,
            'meeting_link' => $this->interview->meeting_link,
        ];
    }
}
