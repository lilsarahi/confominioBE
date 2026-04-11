<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerify;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerificarEmail extends BaseVerify
{
    protected function verificationUrl($notifiable): string
    {
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        $frontend = env('FRONTEND_URL', 'http://localhost:5173');
        return $frontend . '/verificar-email?url=' . urlencode($signedUrl);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifica tu correo electrónico — Condominios')
            ->greeting('¡Hola ' . ($notifiable->persona->nombre ?? '') . '!')
            ->line('Gracias por registrarte. Verifica tu correo haciendo clic en el botón.')
            ->action('Verificar correo', $this->verificationUrl($notifiable))
            ->line('Este enlace expira en 60 minutos.')
            ->salutation('Condominius');
    }
}