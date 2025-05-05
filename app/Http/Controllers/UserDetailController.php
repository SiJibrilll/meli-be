<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserDetailController extends Controller
{
    function update(Request $request) {
        $request->validate([
            'birthDate' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
            'gender' => Rule::in(['female', 'male']),
            'bio' => 'nullable|string|max:500',
            'usernane' => 'nullable|string|max:255',
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        //update user data
        $user = $request->user();
        $details = $user->details;

        $user->update([
            'username' => $request->username,
        ]);

        $details->update([
            'birthDate' => $request->birthDate,
            'address' => $request->address,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'bio' => $request->bio,
            'image_id' => $request->image_id,
        ]);

    
        
        // Return a JSON response with the updated user data
        return response()->json([
            'message' => 'User details updated successfully',
            'userDetails' => [
                'id' => $user->id,
                'username' => $user->username,
                'birthDate' => $details->birthDate,
                'address' => $details->address,
                'phone' => $details->phone,
                'gender' => $details->gender,
                'bio' => $details->bio,
                'image' => $details->image,
            ],
            
        ], 200);
    }
}
