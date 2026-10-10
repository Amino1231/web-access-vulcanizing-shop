<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset your VulcanPro password')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We received a request to reset the password for your VulcanPro account.')
            ->action('Set a new password', $url)
            ->line('This link is valid for **60 minutes** from the time this email was sent.')
            ->line('If you didn\'t request a password reset, no action is needed. Your password won\'t be changed until you click the link above and set a new one.');
    }
}