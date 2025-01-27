<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

    public function register(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|max:12',
            'password_confirmation' => 'required|same:password', // Ensure passwords match
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            $msg = $validator->errors()->first();
            return response()->json([
                'status' => false,
                'message' => $msg
            ], 400);
        }

        $data = $validator->validated();

        $data['password'] = Hash::make($data['password']); // Hash the password

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = $user->createToken('authToken')->accessToken;

        return response()->json([
            'status' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }






    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
        $token = $user->createToken('authToken')->accessToken;


            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid email or password',
        ], 401);
    }

    // public function logout(){
    //     Auth::logout();
    //     return response()->json([
    //       'message' => 'logout successful',
    //     ]);
    // }
}
