<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user=$request->user();
        if (!$user)
        {
            return response()->json(["msg"=>"unauthenticated"],401);
        }
        return response()->json($user,200);
    }

    public function update(Request $request)
    {
        $user=$request->user();
        if (!$user)
        {
            return response()->json(["msg"=> "User not Found"],400);
        }
        $validate = $request->validate
        ([
            "name"=>"sometimes|string|min:3|max:255",
            "email"=>"sometimes|email|unique:users,email," . $user->id,
            "password"=> "sometimes|string|min:3|max:15",
            "height"=>"sometimes|numeric|min:0",
            "weight"=>"sometimes|numeric|min:0",
            "blood_type"=>"sometimes|string",
            "gender"=>"sometimes|string",
            "medical_conditions"=>"sometimes|nullable|string"
        ]);
        if (isset($validate['password'])) 
        {
            $validate['password'] = Hash::make($validate['password']);
        }
        $user->update($validate);
        return response()->json($user,200);
    }

    public function updatePassword(Request $request)
    {
        // old password
        $user=$request->user();
        if (!$user)
        {
            return response()->json(['msg'=> 'unauthenticated'],401);
        }
        $validate = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|max:15|confirmed',
        ]);
        if (!Hash::check($validate["current_password"], $user->password))
        {
            return response()->json(["msg"=> "Incorrect"],400);
        }
        $user->update([
            'password' => Hash::make($validate['new_password']),
        ]);
        return response()->json(["msg"=>"paswword updated"],200);
    }
}
