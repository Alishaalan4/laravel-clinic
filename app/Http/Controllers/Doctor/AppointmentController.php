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
        $appointment = Appointment::with('user')
            ->where('doctor_id', $doctor->id)
            ->orderBy('appointment_date')
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
