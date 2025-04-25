<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;


class UserController extends Controller
{

    public function register(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|max:12',
            'password_confirmation' => 'required|same:password',
            'loginWith' => 'required|in:google,apple,email',
            'fcmToken' => 'required',
            'platForm' => 'required',
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

        $data['password'] = Hash::make($data['password']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'loginWith' => $data['loginWith'],
            'fcmToken' => $data['fcmToken'],
            'platForm' => $data['platForm'],

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
        // Validate the input based on the login method
        $validator = Validator::make($request->all(), [
            'loginWith' => 'required|in:google,apple,email',
            'email' => $request->loginWith != 'email' ? 'nullable|email' : 'required|email',
            'socialId' => $request->loginWith != 'email' ? 'required|string' : 'nullable',
            'password' => $request->loginWith == 'email' ? 'required|string|min:6|max:12' : 'nullable',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        if ($request->loginWith == 'google' || $request->loginWith == 'apple') {
            return $this->loginWithSocial($request);
        } elseif ($request->loginWith == 'email') {
            return $this->loginWithEmail($request);
        }
    }

    private function loginWithSocial($request)
    {
        // dd($request);
        $user = User::where('socialId', $request->socialId)->first()
            ?? ($request->email ? User::where('email', $request->email)->first() : null);
        $isNewUser = false;

        if (!$user) {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make(uniqid()),
                'loginWith' => $request->loginWith,
                'socialId' => $request->socialId,
            ]);
            $isNewUser = true;
        } else {
// dd($request->loginWith);

            $user->update([
                'loginWith' => $request->loginWith,
                'socialId' => $request->socialId,
            ]);
        }

        if ($user['profileImage'] != "") {
            $user['profileImage'] = url($user['profileImage']);
        }

        $token = $user->createToken('authToken')->accessToken;

        return response()->json([
            'status' => true,
            'message' => $isNewUser ? 'Successfully registered and logged in' : 'Successfully logged in',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    private function loginWithEmail($request)
    {
        // dd('email');

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            $user->update([
                'loginWith' => 'email',
                'socialId' => null,
            ]);

            $token = $user->createToken('authToken')->accessToken;

            return response()->json([
                'status' => true,
                'message' => 'Login Successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }
    }
    public function updateUser(Request $req){
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $tableColumns = Schema::getColumnListing('users');

        $data = $req->all();
        // dd($data);

        $filteredData = array_filter($data, function($key) use ($tableColumns) {
            return in_array($key, $tableColumns) && $key !== 'profileImage';
        }, ARRAY_FILTER_USE_KEY);

        if (isset($data['profileImage'])) {
            $filteredData['profileImage'] = $this->handleImageUpload($data['profileImage'], 'profileImage');
        }

        $userId = Auth::user()->id;

        $updateUser = User::where('id', $userId)->update($filteredData);

        return response()->json([
            'message' => 'User Updated Successfully',
            'status' => true
        ], 200);
    }
    public function handleImageUpload($image, $type)
    {
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();
        $path = public_path('images/' . $type . '/' . $filename);
        $image->move(public_path('images/' . $type), $filename);
        return 'images/' . $type . '/' . $filename;
    }
    public function getUser()
    {
        $user = Auth::user();
        $user->profileImage = isset($user->profileImage) ? url($user->profileImage) : '';
        // dd( $user);
        return response()->json([
            'status' => true,
            'message' => "Success",
            'user' => $user,
        ]);
    }
    public function changePassword(Request $request)
 {
    $request->validate([
        'currentpassword' => 'required',
        'newpassword' => 'required|string|min:6',
    ]);
    // dd('ok');

    $user = Auth::user();
    if (!Hash::check($request->currentpassword, $user->password)) {
        return response()->json([
            'status' => false,
            'message' => 'Current password is incorrect',
        ], 400);
    }
    $user->password = Hash::make($request->newpassword);
    $user->save();

    return response()->json([
        'status' => true,
        'message' => 'Password updated successfully',
    ], 200);
}
public function forgotPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
         'email' => 'required|email|exists:users,email'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ], 400);
    }

    $user = User::where('email', $request->email)->first();
    $otpCode = random_int(1000, 9999);
// dd($user);
    $user->update([
        'verficiationCode' => $otpCode,
    ]);

    $companyName = "Taskit";

    try {
        Mail::send('forgot_pass_mail', ['otp' => $otpCode, 'companyName' => $companyName], function ($message) use ($user, $companyName) {
            $message->to($user->email, $user->name)
                ->subject('Password Recovery Mail from ' . $companyName)
                ->from('wasifbaloch527@gmail.com', $companyName);
        });
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to send OTP email. Please try again later.'. $e->getMessage(),
        ], 500);
    }

    return response()->json([
        'status' => true,
        'otp' => $otpCode,
        'message' => 'OTP sent to your email address.',
    ], 200);
}
public function deleteAccount()
{
    $user = Auth::user();

    if ($user) {

        $currentToken = $user->token();
        // dd($currentToken);
        if ($currentToken) {
            $currentToken->revoke();
        }
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'Account deleted successfully',
        ]);
    }

    return response()->json([
        'status' => false,
        'message' => 'User not found',
    ], 404);
}

public function logout()
{
    $user = Auth::user();

    if ($user) {
        $currentToken = $user->token();
        if ($currentToken) {
            $currentToken->revoke();
        }

        return response()->json([
            'status' => true,
            'message' => 'Logged Out Successfully',
        ]);
    }

    return response()->json([
        'status' => false,
        'message' => 'User not authenticated',
    ], 401);
}

public function resetPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'otp' => 'required|integer',
        'password' => Hash::make($request->newPassword),
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ], 400);
    }

    $user = User::where('email', $request->email)->first();

    if ($user->verficiationCode != $request->otp) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid OTP.',
        ], 400);
    }

    // if ($user->otp_expires_at < now()) {
    //     return response()->json([
    //         'status' => false,
    //         'message' => 'OTP has expired.',
    //     ], 400);
    // }

    $user->update([
        'password' => Hash::make($request->password),
        'verficiationCode' => null,
        // 'otp_expires_at' => null,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Password reset successfully.',
    ], 200);
}


}
