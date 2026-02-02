<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    public function register(Request $request)
    {
        $validate = $request->validate([
            "name"=>"required|string",
            "email"=> "required|email|unique:users,email",
            "password"=> "required|string|min:6|max:15",
            ]);
            $admin=Admin::create([
                "name"=>$validate["name"],
                "email"=>$validate["email"],
                "password"=>Hash::make($validate["password"]),
            ]);
            return response()->json(["msg"=>"admin created success","admin"=>$admin],201);
    }
    public function login(Request $request)
    {
        $validate = $request->validate([
            "email"=> "required|email",
            "password"=> "required|string|min:6|max:15"
            ]);
        $admin = Admin::where("email", $request->email)->first();
        if (!$admin || !Hash::check($validate["password"], $admin->password))
        {
            return response()->json([
                "msg"=>"invalid email or password"
            ],401); 
        }
        $token = $admin->createToken('auth_token')->plainTextToken;
        return response()->json([
            "message"=>"Login Successfull",
            "token"=> $token,
            "admin"=>$admin
            ]);
    }
}
