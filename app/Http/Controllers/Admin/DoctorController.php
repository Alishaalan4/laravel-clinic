<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::all();
        return response()->json($doctors);
    }
    public function store(Request $request)
    {
            $data = $request->validate([
            'name'           => 'required|string|min:3',
            'email'          => 'required|email|unique:doctors,email',
            'password'       => 'required|min:6',
            'specialization' => 'required|string',
            'gender'         => 'required|in:male,female',
            'height'         => 'nullable|numeric',
            'weight'         => 'nullable|numeric',
            'blood_type'     => 'required|in:A+,A-,B+,B-,O+,O-,AB+,AB-',
        ]);

        $doctor = Doctor::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => bcrypt($data['password']),
            'specialization' => $data['specialization'],
            'gender'         => $data['gender'],
            'height'         => $data['height'] ?? null,
            'weight'         => $data['weight'] ?? null,
            'blood_type'     => $data['blood_type'],
        ]);

        return response()->json([
            'message' => 'Doctor created successfully',
            'doctor'  => $doctor
        ], 201);
    }
    public function destroy($id)
    {
        $doctor = Doctor::find($id);
        if (! $doctor) 
        {
            return response()->json(["msg"=>"Doctor Not Found"]);
        }
        $doctor->delete();
        return response()->json([
            'message' => 'Doctor deleted successfully'
        ]);
    }
    public function show($id)
    {
        $doctor = Doctor::find($id);
        return response()->json($doctor);
    }
}
