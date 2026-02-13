<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();
        return response()->json(["msg"=> 'user deleted successfully']);
    }
    public function show($id)
    {
        $user = User::find($id);
        return response()->json($user);
    }
    public function changePassword(Request $request,$id)
    {
        $data = $request->validate([
            'new_password'     => 'required|string|min:6',
            'confirm_password' => 'required|string|same:new_password',
        ]);
        $user = User::find($id);
        $user->update([
            'password' => bcrypt($data['new_password'])
        ]);

        return response()->json([
            'message' => 'User password updated successfully by admin'
        ]);
    }
    public function store(Request $request)
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
}
