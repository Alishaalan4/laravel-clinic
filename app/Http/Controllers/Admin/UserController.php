<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

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
}
