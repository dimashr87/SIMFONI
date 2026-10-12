<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class FenomenaNotification extends Notification
{
    use Queueable;

    public $fenomena;
    public $message;

    public function __construct($fenomena, $message)
    {
        $this->fenomena = $fenomena;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        // We want to store notifications in the database
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'fenomena_id' => $this->fenomena->id,
            'message' => $this->message,
            'created_at' => now(),
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'fenomena_id' => $this->fenomena->id,
            'message' => $this->message,
        ];
    }
}

