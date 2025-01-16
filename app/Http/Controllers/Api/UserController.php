<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function showInfo(Request $request)
    {
        $user = Auth::user();

        if (!$user)
        {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        return response()->json($user);

    }

    public function changePass(Request $request)
    {
        $credentials = $request->only('current_password', 'new_password', 'confirm_password');

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        if (!Hash::check($credentials['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        if ($credentials['new_password'] !== $credentials['confirm_password']) {
            return response()->json(['message' => 'Passwords do not match'], 422);
        }

        $user->password = bcrypt($credentials['new_password']);
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function getStudents()
    {
        $studendts = User::where('role_id','=',2)->get();

        return response()->json([
            'message' => 'List of students',
            'Students' => $studendts
        ]);
    }

    public function getProfessors()
    {
        $professors = User::where('role_id','=',3)->get();

        return response()->json([
            'message' => 'List of professors',
            'Professors' => $professors
        ]);
    }
}
