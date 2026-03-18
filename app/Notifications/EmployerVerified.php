<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployerVerified extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public bool $isVerified) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $company = $notifiable->company;
        $companyName = $company?->name ?? 'Your company';

        if ($this->isVerified) {
            return (new MailMessage)
                ->subject('Your company has been verified!')
                ->greeting("Hello {$notifiable->name}!")
                ->line("Great news! Your company profile for {$companyName} has been verified by our admin team.")
                ->line('This verification confirms that your company is legitimate and trustworthy.')
                ->action('View Your Profile', url('/employer/dashboard'))
                ->line('Thank you for using Gridspace!');
        }

        return (new MailMessage)
            ->subject('Your company verification has been removed')
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your company profile for {$companyName} verification status has been changed.")
            ->line('If you believe this is an error, please contact our support team.')
            ->action('Contact Support', url('/support'))
            ->line('Thank you for using Gridspace!');
    }

    public function toArray(object $notifiable): array
    {
        $company = $notifiable->company;
        $companyName = $company?->name ?? 'Your company';

        return [
            'type' => 'employer_verified',
            'is_verified' => $this->isVerified,
            'company_name' => $companyName,
            'message' => $this->isVerified
                ? "Your company {$companyName} has been verified!"
                : "Your company {$companyName} verification has been removed.",
        ];
    }
}
