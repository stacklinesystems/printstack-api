<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Device;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Check credentials
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        // 3. Create / update device (computer)
        $device = Device::updateOrCreate(
            [
                'name' => $request->device_name,
            ],
            [
                'user_id' => $user->id,
                'is_online' => true,
                'last_seen' => now()
            ]
        );

        // 4. Create Sanctum token (device-based)
        $token = $user->createToken($request->device_name)->plainTextToken;

        // 5. Response
        return response()->json([
            'user' => $user,
            'device' => $device,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $device = Device::where('name', $request->device_name)->first();

        if ($device) {
            $device->update([
                'is_online' => false,
                'last_seen' => now()
            ]);
        }

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
