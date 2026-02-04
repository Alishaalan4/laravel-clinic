<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoctorAvailability;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $doctor = $request->user();
        if (! $doctor) 
        {
            return response()->json([["message"=> "Doctor not found"]]);
        }
        return response()->json($doctor->availability());
    }
    public function store(Request $request)
    {
            $doctor = $request->user();

        $data = $request->validate([
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // One availability per day (overwrite)
        DoctorAvailability::updateOrCreate(
            [
                'doctor_id' => $doctor->id,
                'day_of_week' => $data['day_of_week']
            ],
            [
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time']
            ]
        );

        return response()->json([
            'message' => 'Availability saved successfully'
        ]);
    }
}
