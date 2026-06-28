<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        $query = http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $url = rtrim(config('app.frontend_url'), '/').'/reset-password?'.$query;

        return (new MailMessage())
            ->subject('Reset your Baking Store password')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We received a request to reset your password.')
            ->action('Reset password', $url)
            ->line('This link expires in '.config('auth.passwords.users.expire').' minutes.')
            ->line('If you did not request this reset, you can safely ignore this email.');
    }
}
