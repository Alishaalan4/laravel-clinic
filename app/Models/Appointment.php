<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Notifications\AppointmentStatusNotification;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'status',
        'file_upload',
        'cancel_reason',
    ];

protected $casts = [
    'appointment_date' => 'date:Y-m-d',
    'appointment_time' => 'string',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function notifyStatus(string $status, string $actor): void
    {
        $this->loadMissing(['user', 'doctor']);

        $notification = new AppointmentStatusNotification($this, $status, $actor);

        if ($this->user) {
            $this->user->notify($notification);
        }

        if ($this->doctor) {
            $this->doctor->notify($notification);
        }
    }
}
