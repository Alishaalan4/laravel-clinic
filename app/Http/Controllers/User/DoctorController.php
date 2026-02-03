<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\DoctorAvailability;

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
        $request->validate([
            'search' => 'required|string|min:1',
        ]);
        $search = $request->search;
        $doctors = Doctor::where('name', 'LIKE', "%{$search}%")
            ->orWhere('specialization', 'LIKE', "%{$search}%")
            ->get();

        if ($doctors->isEmpty()) {
            return response()->json([
                'msg' => 'No doctors found'
            ], 404);
        }
        return response()->json($doctors, 200);
    }

    public function availability($id)
    {
        // get availability by doctor id
        $doctor = Doctor::find($id);
        if (!$doctor)
        {
            return response()->json(['msg'=> 'Doctor Not Found']);
        }
        $doctor_availability = DoctorAvailability::where('doctor_id',$doctor->id)->get();
        if ($doctor_availability->isEmpty() || $doctor_availability->count() == 0)
        {
            return response()->json(['msg'=> 'No Availibility Found'],404);
        }
        return response()->json($doctor_availability, 200);
    }
}
