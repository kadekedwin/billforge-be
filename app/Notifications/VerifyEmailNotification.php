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

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        try {
            Log::info('toMail() method called for: ' . $notifiable->email);
            
            $verificationUrl = $this->verificationUrl($notifiable);
            Log::info('Verification URL: ' . $verificationUrl);

            $mailMessage = (new MailMessage)
                ->subject('Verify Email Address')
                ->line('Please click the button below to verify your email address.')
                ->action('Verify Email Address', $verificationUrl)
                ->line('If you did not create an account, no further action is required.');

            Log::info('MailMessage created successfully');
            
            return $mailMessage;
        } catch (\Exception $e) {
            Log::error('Error in toMail(): ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

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

        // Extract id and hash from the URL
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $queryParams);
        
        // Build the frontend URL with the verification parameters
        return $frontendUrl . '/verify-email?' . http_build_query([
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
            'expires' => $queryParams['expires'],
            'signature' => $queryParams['signature'],
        ]);
    }
}