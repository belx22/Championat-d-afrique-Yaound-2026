<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationStatusNotification extends Notification
{
    public function __construct(
        public string $step,
        public string $status
    ) {}


    public function via($notifiable)
        {
            return ['database'];
        }

public function toDatabase($notifiable)
{
    return [
        'title' => 'Provisional Registration',
        'message' => "Votre inscription provisional a été {$this->status}",
        'status' => $this->status,
        'url' => '/provisional-registration'
    ];
}

}

