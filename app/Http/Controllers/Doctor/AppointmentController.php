<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\Storage;

class AppointmentController extends Controller
{
    private function authorizeDoctor(Appointment $appointment, Request $request)
    {
        if ($appointment->doctor_id !== $request->user()->id) 
        {
            abort(403, 'Unauthorized');
        }
    }

    public function index(Request $request)
    {
        $doctor = $request->user();
        if (! $doctor)
        {
            return response()->json(["msg"=>"Doctor not found"]);
        }
        
        $query = Appointment::with('user')
            ->where('doctor_id', $doctor->id);

        // Filter by Patient Name
        if ($request->filled('patient_name')) {
            $name = $request->patient_name;
            $query->whereHas('user', function ($q) use ($name) {
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

        $appointment = $query->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get()
            ->map(function (Appointment $item) {
                $item->file_url = $item->file_upload
                    ? url('storage/' . $item->file_upload)
                    : null;
                return $item;
            });
        return response()->json($appointment);
    }
    public function show($id, Request $request)
    {
        $appointment = Appointment::with('user')->find($id);
        if (! $appointment)
        {
            return response()->json(['msg'=> 'Appointment not found'], 404);
        }
        
        $this->authorizeDoctor($appointment, $request);

        $appointment->file_url = $appointment->file_upload
            ? url('storage/' . $appointment->file_upload)
            : null;

        return response()->json($appointment);
    }

    public function accept(Appointment $appointment, Request $request)
    {
        $this->authorizeDoctor($appointment, $request);
        if ($appointment->status != 'pending')
        {
            return response()->json(['msg'=> 'Appointment Cannot be Accepted'],422);
        }
        $appointment->update(['status'=> 'booked']);
        $appointment->notifyStatus('booked', 'doctor');
        return response()->json(['msg'=> 'Appointment Accepted'],200);
    }

    public function cancel(Appointment $appointment, Request $request)
    {
        $this->authorizeDoctor($appointment, $request);
        $validate = $request->validate(
            [
                'reason'=>'required|string|min:3'
            ]);
        $appointment->update(['status'=> 'canceleld','cancel_reason'=>$validate['reason']]);
        $appointment->notifyStatus('canceleld', 'doctor');
        return response()->json(['msg'=> 'Appointment cancelled'],200);
    }
    public function complete(Appointment $appointment, Request $request)
    {
        $this->authorizeDoctor($appointment, $request);
        if ($appointment->status != 'booked')
        {
            return response()->json(['msg'=> 'Only booked appointments can be marked completed'],400);
        }
        $appointment->update(['status'=> 'completed']);
        $appointment->notifyStatus('completed', 'doctor');
        return response()->json(['msg'=> 'Appointment is Completed'],200);
    }

    public function file(Appointment $appointment, Request $request)
    {
        $this->authorizeDoctor($appointment, $request);

        if (! $appointment->file_upload) {
            return response()->json(['msg' => 'No file uploaded for this appointment'], 404);
        }

        if (! Storage::disk('public')->exists($appointment->file_upload)) {
            return response()->json(['msg' => 'File not found'], 404);
        }

        $filePath = Storage::disk('public')->path($appointment->file_upload);
        return response()->download($filePath);
    }

// ready functions in doctor model
    public function stats(Request $request)
    {
        $doctor= $request->user();
        return response()->json([
            'total Appointments' => $doctor->totalAppointments(),
            'pending' => $doctor->pendingAppointments(),
            'booked' => $doctor->bookedAppointments(),
            'completed' => $doctor->completedAppointments(),
            'cancelled' => $doctor->cancelledAppointments(),
        ]);
    }
}
