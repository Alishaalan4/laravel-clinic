<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;
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
        $appointment = Appointment::with('user')->where('doctor_id', $doctor->id)->orderBy('appointment_date')->orderBy('appointment_time')->get();
        return response()->json($appointment);
    }

    public function accept(Appointment $appointment, Request $request)
    {
        $this->authorizeDoctor($appointment, $request);
        if ($appointment->status != 'pending')
        {
            return response()->json(['msg'=> 'Appointment Cannot be Accepted'],422);
        }
        // email implementation when we setup the mail system
        $appointment->update(['status'=> 'booked']);
        return response()->json(['msg'=> 'Appointment Accepted'],200);
    }

    public function cancel(Appointment $appointment, Request $request)
    {
        $this->authorizeDoctor($appointment, $request);
        $validate = $request->validate(
            [
                'reason'=>'required|string|min:3'
            ]);
        $appointment->update(['status'=> 'cancelled','cancel_reason'=>$validate['reason']]);
        // email sending 
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
        return response()->json(['msg'=> 'Appointment is Completed'],200);
    }

    public function stats(Request $request)
    {
        $doctor= $request->user();
        return response()->json([
            'total Appointments' => $doctor->appointments()->count(),
            'pending' => $doctor->appointments()->where('status','pending')->count(),
            'booked' => $doctor->appointments()->where('status','booked')->count(),
            'completed' => $doctor->appointments()->where('status','completed')->count(),
            'cancelled' => $doctor->appointments()->where('status','cancelled')->count(),
        ]);
    }
}
