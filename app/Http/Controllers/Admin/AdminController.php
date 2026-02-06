<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Facades\App;

class AdminController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            "name"=> "required|string|min:3",
            "email"=> "required|email|unique:admins,email",
            "password"=> "required|string|min:6",
        ]);
        $admin = Admin::create([
            "name"=> $data["name"],
            "email"=> $data["email"],
            "password"=> bcrypt($data["password"]),
        ]);
        return response()->json(["msg"=>"admin created","admin"=>$admin]);
    }
    public function stats()
    {
        return response()->json(
        [
                'Users'=> User::count(),
                'Doctors' => Doctor::count(),
                'Admins' => Admin::count(),
                'Total Appointments'=>Appointment::count(),
                'pending Appointments'=>Appointment::where('status','pending')->count(),
                'booked Appointments'=>Appointment::where('status','booked')->count(),
                'completed Appointments'=>Appointment::where('status','completed')->count(),
                'cancelled Appointments'=>Appointment::where('status','canceleld')->count()
                ]
            );
    }
    public function index()
    {
        $admins = Admin::all();
        return response()->json($admins);
    }
    public function show($id)
    {
        $admin = Admin::find($id);
        return response()->json($admin);
    }
    public function changePassword(Request $request, $id)
    {
        $data = $request->validate([
            'new_password'     => 'required|string|min:6',
            'confirm_password' => 'required|string|same:new_password',
        ]);
        $admin = Admin::find($id);
        $admin->update([
            'password' => Hash::make($data['new_password'])
        ]);

        return response()->json([
            'message' => 'User password updated successfully by admin'
        ]);
        

    }
}
