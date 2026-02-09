<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Doctor;
use Carbon\Carbon;

class AppointmentController extends Controller
{
        public function index()
    {
        return response()->json(
            Appointment::with(['doctor', 'user'])->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'          => 'required|exists:users,id',
            'doctor_id'        => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
        ]);

        $start = Carbon::createFromFormat('H:i', $data['appointment_time']);
        $end   = $start->copy()->addMinutes(30);

        $appointment = Appointment::create([
            'user_id'          => $data['user_id'],
            'doctor_id'        => $data['doctor_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $start->format('H:i'),
            'end_time'         => $end->format('H:i'),
            'status'           => 'booked',
        ]);

        $appointment->notifyStatus('booked', 'admin');

        return response()->json([
            'message' => 'Appointment created by admin',
            'appointment' => $appointment
        ], 201);
    }
    public function show($id)
    {
        $appointment = Appointment::find($id);
        return response()->json($appointment);
    }
}
