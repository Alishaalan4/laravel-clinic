<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use Illuminate\Support\Carbon;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointment = Appointment::with(['doctor', 'user'])->latest()->paginate(10);
        if ($appointment->isEmpty()) 
        {
            return response()->json(["msg"=>"No Available Appointments"]);
        }
        return response()->json($appointment);
    }
    public function show($id)
    {
        $appointment = Appointment::with(['doctor', 'user'])->find($id);
        if (!$appointment)    
        {
            return response()->json(["msg"=> "no available appointment"]);
        }
        return response()->json($appointment);
    }   
    public function search(Request $request)
    {
        $user = $request->user();

        $query = Appointment::with(['doctor'])
            ->where('user_id', $user->id);

        // Filter by Doctor Name
        if ($request->filled('doctor_name')) {
            $name = $request->doctor_name;
            $query->whereHas('doctor', function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%");
            });
        }

        // Filter by Date
        if ($request->filled('date')) {
            $query->where('appointment_date', $request->date);
        }

        // Filter by Time
        if ($request->filled('time')) {
            $time = $request->time;
            // Handle cases where DB might store 9:30:00 but input is 09:30, or vice versa
            $query->where(function($q) use ($time) {
                // Try exact match or substring
                $q->where('appointment_time', 'like', "%{$time}%");
                
                // If input has leading zero (e.g. 09:30), also try without it (e.g. 9:30)
                if (substr($time, 0, 1) === '0') {
                    $noZero = substr($time, 1);
                    $q->orWhere('appointment_time', 'like', "%{$noZero}%");
                }
            });
        }

        $appointments = $query->latest()->paginate(10);

        return response()->json($appointments);
    }
    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'doctor_id'        => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Check doctor exists
        $doctor = Doctor::findOrFail($data['doctor_id']);

        // check doctor availability for that date
        $availability = DoctorAvailability::where('doctor_id', $doctor->id)
            ->where('date', $data['appointment_date'])
            ->where('start_time', $data['appointment_time'])
            ->first();

        if (!$availability) {
            return response()->json([
                'message' => 'Selected time is not one of the doctor available blocks'
            ], 422);
        }

        // Check block availability
        $exists = Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $data['appointment_time'])
            ->whereIn('status', ['pending','booked'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This time slot is already booked'
            ], 409);
        }

        //  Handle file upload (optional)
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('appointments', 'public');
        }

        //  Create appointment
        $appointment= Appointment::create([
            'user_id'          => $user->id,
            'doctor_id'        => $data['doctor_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'file_upload'      => $filePath,
            'status'           => 'pending',
        ]);

        $appointment->notifyStatus('pending', 'user');

        return response()->json([
            'message' => 'Appointment booked successfully and is pending doctor approval',
            'appointment' => $appointment
        ], 201);
    }
}
