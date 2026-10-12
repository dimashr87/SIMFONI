<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventNotification extends Notification
{
    use Queueable;

    protected $event;
    protected $type;

    public function __construct($event, $type)
    {
        $this->event = $event;
        $this->type = $type; // Can be 'reminder', 'approved', or 'rejected'
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // You can add more channels if needed
    }

    public function toMail($notifiable)
    {
        $message = new MailMessage;

        if ($this->type == 'reminder') {
            $message->line('Reminder: Your event is coming up in 1 day.')
                    ->line('Event: ' . $this->event->title)
                    ->line('Date: ' . $this->event->date);
        } elseif ($this->type == 'approved') {
            $message->line('Your event has been approved.')
                    ->line('Event: ' . $this->event->title);
        } elseif ($this->type == 'rejected') {
            $message->line('Your event has been rejected.')
                    ->line('Event: ' . $this->event->title);
        }

        return $message->action('View Event', url('/events/' . $this->event->id))
                       ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'type' => $this->type,
        ];
    }
}
