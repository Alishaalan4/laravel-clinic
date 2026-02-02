<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DoctorAuthController extends Controller
{
    public function register(Request $request)
    {
        $validate = $request->validate([
            "name"=>"required|string|min:3|max:255",
            "email"=>"required|email|unique:users,email",
            "password"=> "required|string|min:3|max:15",
            "height"=>"required|numeric|min:0",
            "weight"=>"required|numeric|min:0",
            "blood_type"=>"required|string",
            "gender"=>"required|string",
            "specialization"=>"required|string"
        ]);

        $doctor =Doctor::create([
            "name"=> $validate["name"],
            "email"=> $validate["email"],
            "password"=> Hash::make($validate["password"]),
            "height"=>$validate["height"],
            "weight"=>$validate["weight"],
            "gender"=> $validate["gender"],
            "blood_type"=>$validate["blood_type"],
            "specialization"=>$validate["specialization"],
        ]);
        return response()->json(["msg"=>"Doctor created successfully","Doctor"=>$doctor],201);
    }

    public function login(Request $request)
    {
        $validate = $request->validate
        ([
            "email"=> "required|string",
            "password"=> "required|string",
        ]);
        $doctor= Doctor::where("email", $validate["email"])->first();
        if (!$doctor || !Hash::check($validate["password"], $doctor->password)) 
        {
            return response()->json(["msg"=>"Invalid email or password"],401);
        }
        $token=$doctor->createToken("auth_token")->plainTextToken;
        return response()->json([
            "message"=>"Login Suceessfull",
            "doctor"=>$doctor,
            "token"=>$token
        ],200);
    }
}
