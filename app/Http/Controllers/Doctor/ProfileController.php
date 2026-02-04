<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $doctor = $request->user();
        if (! $doctor) 
        {
            return response()->json(["msg"=>"Doctor Not Found"]);
        }
        return response()->json($doctor);
    }
    public function update(Request $request)
    {
        $doctor = $request->user();
        if (! $doctor)
        {
            return response()->json(["msg"=> "no doctor found"]);
        }
        $validate = $request->validate(
        [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:doctors,email,' . $doctor->id,
            'height' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,O+,O-,AB+,AB-',
            'gender' => 'nullable|in:male,female',
            'specialization' => 'nullable|string|max:255',
            'password' => 'nullable|min:6'
        ]);
        if (isset($data['password'])) 
        {
            $data['password'] = Hash::make($validate['password']);
        }
        $doctor->update($validate);
        return response()->json(['msg'=> 'Profile updates',$doctor]);
    }
}
