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
            'appointment_time' => 'required|string',
        ]);

        $start = $this->parseAppointmentTime((string) $data['appointment_time']);

        if (!$start) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'appointment_time' => [
                        'The appointment time field format is invalid.',
                    ],
                ],
            ], 422);
        }

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

    private function parseAppointmentTime(string $input): ?Carbon
    {
        $time = trim($input);
        $time = preg_replace('/\s+/u', ' ', $time) ?? $time;
        $time = strtoupper($time);
        $time = str_replace(['A.M.', 'P.M.', 'A.M', 'P.M'], ['AM', 'PM', 'AM', 'PM'], $time);

        $formats = ['H:i', 'G:i', 'H:i:s', 'G:i:s', 'h:i A', 'g:i A', 'h:i:s A', 'g:i:s A', 'h:iA', 'g:iA'];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $time);
                if ($parsed !== false) {
                    return $parsed;
                }
            } catch (\Throwable $e) {
                // Keep trying known formats.
            }
        }

        return null;
    }
}
