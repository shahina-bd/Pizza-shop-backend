<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // Send OTP
    public function sendOtp(Request $request)
    {
        $request->validate(['mobile' => 'required|string|exists:users,mobile']);
        
        $otp = (string) rand(100000, 999999);
        $user = User::where('mobile', $request->mobile)->first();
        $user->update([
            'otp' => $otp,
            'otp_expire_at' => now()->addMinutes(5)
        ]);

        if ($user->email) {
            try {
                Mail::to($user->email)
                    ->send(new OtpMail($otp, $user->name));
            } catch (Exception $error) {
                Log::error('Failed to send otp:' . $error->getMessage());
            }
        }

        // In real project, send SMS here
        return response()->json([
            'message' => 'OTP sent successfully',
            'otp' => $otp,
        ]);
    }
    
    // Verify OTP & Login
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|exists:users,mobile',
            'otp' => 'required|string|size:6'
        ]);
        
        $user = User::with('roles')
            ->where('mobile', $request->mobile)
            ->where('otp', $request->otp)
            ->where('otp_expire_at', '>', now())
            ->first();
        
        if (!$user) {
            return response()->json(['message' => 'Invalid or expired OTP'], 401);
        }

        $primaryRole = $user->roles->first()?->name;
        $updateData = ['otp' => null, 'otp_expire_at' => null];
        if ($primaryRole && $user->role !== $primaryRole) {
            $updateData['role'] = $primaryRole;
        }
        
        $user->update($updateData);
        $user->load('roles');
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }
    
    // Get Profile
    public function profile(Request $request)
    {
        return response()->json($request->user()?->load('roles'));
    }
    
    // Update Profile
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $user->update($request->only(['name', 'email', 'address']));
        return response()->json(['message' => 'Profile updated', 'user' => $user]);
    }

}
