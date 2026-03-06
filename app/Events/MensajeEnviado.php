<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MensajeEnviado implements ShouldBroadcastNow
{
    use SerializesModels;

    public string $mensaje;
    public string $remitente;

    public function __construct(string $mensaje, string $remitente = 'Usuario')
    {
        $this->mensaje  = $mensaje;
        $this->remitente = $remitente;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('chat');
    }

    public function broadcastAs(): string
    {
        return 'mensaje.enviado';
    }
}