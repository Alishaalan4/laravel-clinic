<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorAvailability extends Model
{
    use HasFactory;
    protected $table = 'doctor_availability';
    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'date',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    // Relation
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
