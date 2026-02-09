<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\CarbonInterface;

class AppointmentStatusNotification extends Notification
{
    use Queueable;

    private Appointment $appointment;
    private string $status;
    private string $actor;

    private const STATUS_LABELS = [
        'pending' => 'Pending',
        'booked' => 'Accepted',
        'completed' => 'Completed',
        'canceleld' => 'Cancelled',
    ];

    public function __construct(Appointment $appointment, string $status, string $actor)
    {
        $this->appointment = $appointment;
        $this->status = $status;
        $this->actor = $actor;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment->loadMissing(['user', 'doctor']);

        $statusLabel = self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);

        $dateValue = $appointment->appointment_date;
        $dateText = $dateValue instanceof CarbonInterface ? $dateValue->format('Y-m-d') : (string) $dateValue;
        $timeText = (string) $appointment->appointment_time;

        $mail = (new MailMessage)
            ->subject("Appointment {$statusLabel}")
            ->greeting('Hello!')
            ->line("Appointment status: {$statusLabel}")
            ->line('Patient: ' . ($appointment->user->name ?? 'N/A'))
            ->line('Doctor: ' . ($appointment->doctor->name ?? 'N/A'))
            ->line("Date: {$dateText}")
            ->line("Time: {$timeText}")
            ->line('Action by: ' . ucfirst($this->actor));

        if ($this->status === 'canceleld' && ! empty($appointment->cancel_reason)) {
            $mail->line('Cancel reason: ' . $appointment->cancel_reason);
        }

        return $mail;
    }
}
