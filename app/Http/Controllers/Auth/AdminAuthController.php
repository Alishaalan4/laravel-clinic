<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use app\Models\Admin;

class AdminAuthController extends Controller
{
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
        $token = $admin->createToken("auth_token")->plainTextToken;
        return response()->json([
            "message"=>"Login Successfull",
            "token"=> $token,
            "admin"=>$admin
            ]);
    }
}
