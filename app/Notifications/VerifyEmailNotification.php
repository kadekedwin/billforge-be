<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        $subject = 'Verify Email Address';
        $body = "Hello,\n\nPlease click the link below to verify your email address:\n\n$verificationUrl\n\nIf you did not create an account, no further action is required.";

        $email = (new MailtrapEmail())
            ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
            ->to(new Address($notifiable->email))
            ->subject($subject)
            ->text($body);

        MailtrapClient::initSendingEmails(apiKey: env('MAIL_PASSWORD'))
            ->send($email);

        return (new MailMessage)
            ->subject($subject)
            ->line('A verification email has been sent.');
    }

    // public function toMail($notifiable)
    // {
    //     try {
    //         $verificationUrl = $this->verificationUrl($notifiable);

    //         $mailMessage = (new MailMessage)
    //             ->subject('Verify Email Address')
    //             ->line('Please click the button below to verify your email address.')
    //             ->action('Verify Email Address', $verificationUrl)
    //             ->line('If you did not create an account, no further action is required.');

    //         return $mailMessage;
    //     } catch (\Exception $e) {
    //         throw $e;
    //     }
    // }

    protected function verificationUrl($notifiable)
    {
        $frontendUrl = config('app.frontend_url');
        
        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $queryParams);
        
        return $frontendUrl . '/verify-email?' . http_build_query([
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
            'expires' => $queryParams['expires'],
            'signature' => $queryParams['signature'],
        ]);
    }
}