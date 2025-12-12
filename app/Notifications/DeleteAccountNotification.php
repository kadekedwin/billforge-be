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

class DeleteAccountNotification extends Notification implements ShouldQueue
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
        $deletionUrl = $this->deletionUrl($notifiable);

        $subject = 'Account Deletion Confirmation';
        $body = "Hello,\n\nWe received a request to delete your account.\n\n⚠️ WARNING: This action is permanent and cannot be undone. All your data including businesses, transactions, and customers will be permanently deleted.\n\nIf you wish to proceed, please click the link below:\n\n$deletionUrl\n\nThis link will expire in 30 minutes.\n\nIf you did not request account deletion, please ignore this email and your account will remain active.";

        $email = (new MailtrapEmail())
            ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
            ->to(new Address($notifiable->email))
            ->subject($subject)
            ->text($body);

        MailtrapClient::initSendingEmails(apiKey: env('MAIL_PASSWORD'))
            ->send($email);

        return (new MailMessage)
            ->subject($subject)
            ->line('An account deletion email has been sent.');
    }

    protected function deletionUrl($notifiable)
    {
        $frontendUrl = config('app.frontend_url');

        $url = URL::temporarySignedRoute(
            'account.delete',
            now()->addMinutes(30),
            [
                'token' => $this->token,
                'email' => $notifiable->email
            ]
        );

        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $queryParams);

        return $frontendUrl . '/confirm-account-deletion?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->email,
            'expires' => $queryParams['expires'],
            'signature' => $queryParams['signature'],
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
