<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Appointment;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::all();
        if (!$doctors)
        {
            return response()->json(["msg"=>"No Doctors Found"]);
        }
        return response()->json($doctors);
    }
    public function show($id)
    {
        $doctor = Doctor::find($id);
        if (!$doctor)
        {
            return response()->json(["msg"=> "No Doctor Found"]);
        }
        return response()->json($doctor);
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        if (!$query) {
            return response()->json([
                'message' => 'Search query is required',
                'doctors' => []
            ], 422);
        }

        $doctors = Doctor::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('specialization', 'LIKE', "%{$query}%");
            })
            ->select('id','name','specialization','gender')
            ->get();
        return response()->json([
            'doctors' => $doctors
        ]);
    }

    public function availability(Request $request, Doctor $doctor)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today'
        ]);

        $date = $request->date;
        $dayName = date('l', strtotime($date));

        // Doctor working hours
        $availability = DoctorAvailability::where('doctor_id', $doctor->id)
            ->where('day_of_week', $dayName)
            ->first();

        if (!$availability) {
            return response()->json([
                'message' => 'Doctor is not available on this day',
                'slots' => []
            ]);
        }

        // Already booked times
        $bookedTimes = Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', $date)
            ->whereIn('status', ['pending','booked'])
            ->pluck('appointment_time')
            ->toArray();

        // Generate 30-minute slots
        $slots = [];
        $start = strtotime($availability->start_time);
        $end   = strtotime($availability->end_time);

        while ($start < $end) {
            $time = date('H:i', $start);

            if (!in_array($time, $bookedTimes)) {
                $slots[] = $time;
            }

            $start = strtotime('+30 minutes', $start);
        }

        return response()->json([
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'specialization' => $doctor->specialization,
            ],
            'date' => $date,
            'available_slots' => $slots
        ]);
    }
}
