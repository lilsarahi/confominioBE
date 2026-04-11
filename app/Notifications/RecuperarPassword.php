<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RecuperarPassword extends Notification
{
    public function __construct(private string $code) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Código de recuperación de contraseña — Condominius')
            ->greeting('¡Hola ' . ($notifiable->persona->nombre ?? '') . '!')
            ->line('Recibimos una solicitud para restablecer tu contraseña.')
            ->line('Tu código de verificación es:')
            ->line('**' . $this->code . '**')
            ->line('Este código expira en **15 minutos**.')
            ->line('Si no solicitaste este cambio, ignora este correo.')
            ->salutation('Condominius');
    }
}