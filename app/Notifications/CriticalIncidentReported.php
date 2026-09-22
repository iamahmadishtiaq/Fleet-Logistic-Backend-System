<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalIncidentReported extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Incident $incident)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $trip = $this->incident->trip;
        $vehicle = $trip->vehicle;
        $driver = $trip->driver;

        return (new MailMessage)
            ->error()
            ->subject("EMERGENCY ALERT: {$this->incident->type} on Trip #{$trip->trip_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A critical incident has been reported on an active transit route")
            ->line("**Trip Number:** {$trip->trip_number}")
            ->line("**Vehicle:**" . ($vehicle ? "{$vehicle->plate_number} ({$vehicle->make} {$vehicle->model})" : 'N/A'))
            ->line("**Driver:**" . ($driver ? "{$driver->name} ({$driver->phone})" : 'N/A'))
            ->line("**Incident Type:**" . strtoupper($this->incident->type))
            ->line("**Severity:**" . strtoupper($this->incident->severity))
            ->line("**Description:** {$this->incident->description}")
            ->line("**Estimated Delay:** {$this->incident->estimated_delay_hours} Hours")
            ->action('View Live Map', url("/api/fleet/live-map"))
            ->line('Immediate operational coordination is recommended.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
