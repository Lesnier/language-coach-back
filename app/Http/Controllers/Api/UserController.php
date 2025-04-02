<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

        /** @var User $user */ // Explicitly specify the type
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
        $user->save(); // Now the save method will work correctly

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function getStudents()
    {
        $students = User::where('role_id', '=', 2)->get();

        // Add profile_picture_url to each student
        $students = $students->map(function ($student) {
            if ($student->profile_picture) {
                $student->profile_picture_url = asset('storage/' . $student->profile_picture);
            }
            return $student;
        });

        return response()->json([
            'message' => 'List of students',
            'Students' => $students
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

    public function uploadProfilePicture(Request $request)
    {
        /** @var User $user */ // Explicitly specify the type
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $file = $request->file('profile_picture');
        $path = $this->storeFile($file, 'profile_pictures');
        $user->profile_picture = $path;
        $user->save(); // Now the save method will work correctly

        return response()->json([
            'message' => 'Profile picture uploaded successfully',
            'profile_picture_url' => asset('storage/' . $path)
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var User $user */ // Explicitly specify the type
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'birth_date' => 'sometimes|date',
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'sometimes|required_with:new_password,confirm_password',
            'new_password' => 'sometimes|required_with:current_password|same:confirm_password', // Use 'same:confirm_password'
        ]);

        // Update name and birth_date
        if (isset($validatedData['name'])) {
            $user->name = $validatedData['name'];
        }
        if (isset($validatedData['birth_date'])) {
            $user->birth_date = $validatedData['birth_date'];
        }

        // Update profile picture
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $file = $request->file('profile_picture');
            $path = $this->storeFile($file, 'profile_pictures');
            $user->profile_picture = $path;
        }

        // Update password
        if (isset($validatedData['current_password'])) {
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 422);
            }
            $user->password = bcrypt($validatedData['new_password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }

    private function storeFile($file, $directory)
    {
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = str_replace(" ", "_", $filename);
        $extension = $file->getClientOriginalExtension();
        $final_name = date("His") . "_" . $filename . "." . $extension;

        return $file->storeAs($directory, $final_name, 'public');
    }
}
