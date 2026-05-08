<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Device;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validate only auth fields (device is optional)
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Authenticate
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        $device = null;

        // 3. ONLY create/update device if provided
        if ($request->filled('device_name')) {

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
        }

        // 4. Token (still works without device)
        $tokenName = $request->device_name ?? 'api-token';

        $token = $user->createToken($tokenName)->plainTextToken;

        // 5. Response
        return response()->json([
            'user' => $user,
            'device' => $device, // null if not provided
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
