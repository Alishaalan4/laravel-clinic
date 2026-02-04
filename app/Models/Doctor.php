<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Doctor extends Model
{
    use HasFactory,Notifiable,HasApiTokens;
    protected $fillable = [
        'name',
        'email',
        'password',
        'height',
        'weight',
        'blood_type',
        'gender',
        'specialization'
    ];

    protected $casts = [
        'height'=>'float',
        'weight'=>'float'
    ];

    protected $hidden=[
        'password',
    ];

    // relations
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    public function availability()
    {
        return $this->hasMany(DoctorAvailability::class);
    }

    // stats
    public function totalAppointments()
    {
        return $this->appointments()->count();
    }
    public function pendingAppointments()
    {
        return $this->appointments()->where('status','pending')->count();
    }
    public function bookedAppointments()
    {
        return $this->appointments()->where('status','booked')->count();
    }
        public function completedAppointments()
    {
        return $this->appointments()->where('status','completed')->count();
    }
    public function cancelledAppointments()
    {
        return $this->appointments()->where('status','cancelled')->count();
    }
}
