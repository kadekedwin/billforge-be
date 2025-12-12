<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);

        $subject = 'Reset Password Notification';
        $body = "Hello,\n\nYou are receiving this email because we received a password reset request for your account.\n\nPlease click the link below to reset your password:\n\n$resetUrl\n\nThis password reset link will expire in 60 minutes.\n\nIf you did not request a password reset, no further action is required.";

        $email = (new MailtrapEmail())
            ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
            ->to(new Address($notifiable->email))
            ->subject($subject)
            ->text($body);

        MailtrapClient::initSendingEmails(apiKey: env('MAIL_PASSWORD'))
            ->send($email);

        return (new MailMessage)
            ->subject($subject)
            ->line('A password reset email has been sent.');
    }

    protected function resetUrl($notifiable)
    {
        $frontendUrl = config('app.frontend_url');

        $url = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(60),
            [
                'token' => $this->token,
                'email' => $notifiable->email
            ]
        );

        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $queryParams);

        return $frontendUrl . '/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->email,
            'expires' => $queryParams['expires'],
            'signature' => $queryParams['signature'],
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
