<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    function register(Request $request) {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = DB::transaction(function () use ($request) {
            $user = User::create([ // create a new user
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);
        
            $user->details()->create(); // create a new user detail record

            return $user;
        });
        

        // Create a token for the user
        // The token is used for authentication in API requests
        $token = $user->createToken($user->username)->plainTextToken;

        // Return a JSON response with the user data and token
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    function login(Request $request) {
        // Validate the request data
        // Ensure the email and password fields are present and valid
        $request->validate([ 
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Attempt to authenticate the user using the provided email and password
        // If authentication fails, return a 401 Unauthorized response
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // If authentication is successful, retrieve the authenticated user
        // Create a new token for the user
        $user = Auth::user();
        $token = $user->createToken($user->username)->plainTextToken;

        // Return a JSON response with the user data and token
        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $user,
            'token' => $token,
        ], 200);
        
    }
}
