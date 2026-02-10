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
            return response()->json(["message"=> "Doctor not found"], 401);
        }
        return response()->json(
            $doctor->availability()->orderBy('date')->orderBy('start_time')->get()
        );
    }
    public function store(Request $request)
    {
        $doctor = $request->user();
        if (! $doctor)
        {
            return response()->json(["message"=> "Doctor Not found"], 401);
        }

        $data = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $dayName = date('l', strtotime($data['date']));
        $allowedDays = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
        if (!in_array($dayName, $allowedDays, true)) {
            return response()->json([
                'message' => 'Availability must be on a weekday (Monday to Friday)'
            ], 422);
        }

        $overlapExists = DoctorAvailability::where('doctor_id', $doctor->id)
            ->where('date', $data['date'])
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            })
            ->exists();

        if ($overlapExists) {
            return response()->json([
                'message' => 'Availability overlaps an existing block'
            ], 422);
        }

        $availability = DoctorAvailability::create([
            'doctor_id' => $doctor->id,
            'date' => $data['date'],
            'day_of_week' => $dayName,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time']
        ]);

        return response()->json([
            'message' => 'Availability saved successfully',
            'availability' => $availability
        ]);
    }
    public function delete($id)
    {
        $availability = DoctorAvailability::findOrFail($id);
        $availability->delete();
        return response()->json(['message'=> 'Availability Deleted']);
    }
}
