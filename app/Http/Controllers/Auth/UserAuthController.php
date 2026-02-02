<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\User;

/*
    'name',
    'email',
    'password',
    'height',
    'weight',
    'blood_type',
    'gender',
    'medical_conditions'
 */

class UserAuthController extends Controller
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
            "medical_conditions"=>"nullable|string"
        ]);

        $user = User::create([
            "name"=> $validate["name"],
            "email"=> $validate["email"],
            "password"=> Hash::make($validate["password"]),
            "height"=>$validate["height"],
            "weight"=>$validate["weight"],
            "gender"=> $validate["gender"],
            "blood_type"=>$validate["blood_type"],
            "medical_conditions"=>$validate["medical_conditions"] ?? null,
        ]);
        return response()->json(["msg"=>"user created successfully","user"=>$user],201);
    }

    public function login(Request $request)
    {
        $validate = $request->validate
        ([
            "email"=> "required|string",
            "password"=> "required|string",
        ]);
        $user= User::where("email", $validate["email"])->first();
        if (!$user || !Hash::check($validate["password"], $user->password)) 
        {
            return response()->json(["msg"=>"Invalid email or password"],401);
        }
        $token=$user->createToken("auth_token")->plainTextToken;
        return response()->json([
            "message"=>"Login Suceessfull",
            "user"=>$user,
            "token"=>$token
        ],200);
    }
}
