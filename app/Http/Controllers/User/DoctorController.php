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
        if ($doctors->count() == 0) 
        {
            {
            return response()->json(["msg"=>"No Doctors Found"]);
            }
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

        // Doctor working blocks for a specific date
        $availability = DoctorAvailability::where('doctor_id', $doctor->id)
            ->where('date', $date)
            ->orderBy('start_time')
            ->get();

        if ($availability->isEmpty()) {
            return response()->json([
                'message' => 'Doctor is not available on this day',
                'available_blocks' => []
            ]);
        }

        // Already booked times
        $bookedTimes = Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', $date)
            ->whereIn('status', ['pending','booked'])
            ->pluck('appointment_time')
            ->map(function ($time) {
                return date('H:i', strtotime($time));
            })
            ->toArray();

        // Filter blocks that are already booked (by start_time)
        $availableBlocks = $availability
            ->reject(function ($block) use ($bookedTimes) {
                return in_array($block->start_time, $bookedTimes);
            })
            ->values()
            ->map(function ($block) {
                return [
                    'start_time' => date('H:i', strtotime($block->start_time)),
                    'end_time' => date('H:i', strtotime($block->end_time))
                ];
            });

        return response()->json([
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'specialization' => $doctor->specialization,
            ],
            'date' => $date,
            'available_blocks' => $availableBlocks
        ]);
    }
}
