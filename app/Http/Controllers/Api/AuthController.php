<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'image_size_bytes' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'image_size_bytes' => $request->image_size_bytes
        ]);

        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth_token')->plainTextToken;

        Session::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => json_encode(['token' => hash('sha256', $token)]),
            'last_activity' => time(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Please verify your email address',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        Session::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => json_encode(['token' => hash('sha256', $token)]),
            'last_activity' => time(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $currentToken = $request->bearerToken();

        // Delete only the session for the current token
        if ($currentToken) {
            $hashedToken = hash('sha256', $currentToken);
            Session::where('user_id', $user->id)
                ->where('payload', 'LIKE', '%' . $hashedToken . '%')
                ->delete();
        }

        // Delete only the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'ok'
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link'
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => 200,
                'message' => 'Email already verified'
            ], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully'
        ], 200);
    }

    public function resendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link'
            ], 400);
        }

        $cacheKey = 'email_verification_sent_' . $user->id;
        $delayMinutes = 1;

        if (Cache::has($cacheKey)) {
            $lastSent = Cache::get($cacheKey);
            $nextAllowed = Carbon::parse($lastSent)->addMinutes($delayMinutes);

            if (Carbon::now()->lt($nextAllowed)) {
                $remainingSeconds = Carbon::now()->diffInSeconds($nextAllowed);
                return response()->json([
                    'success' => false,
                    'message' => 'Please wait ' . ceil($remainingSeconds / 60) . ' minutes before requesting another verification email.'
                ], 429);
            }
        }

        $user->sendEmailVerificationNotification();

        Cache::put($cacheKey, Carbon::now(), $delayMinutes * 60); // Cache for 5 minutes

        return response()->json([
            'success' => true,
            'message' => 'Verification link sent!'
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Rate limiting check
        $cacheKey = 'password_reset_sent_' . $user->id;
        $delayMinutes = 1;

        if (Cache::has($cacheKey)) {
            $lastSent = Cache::get($cacheKey);
            $nextAllowed = Carbon::parse($lastSent)->addMinutes($delayMinutes);

            if (Carbon::now()->lt($nextAllowed)) {
                $remainingSeconds = Carbon::now()->diffInSeconds($nextAllowed);
                return response()->json([
                    'success' => false,
                    'message' => 'Please wait ' . ceil($remainingSeconds / 60) . ' minute(s) before requesting another password reset email.'
                ], 429);
            }
        }

        // Generate token
        $token = Str::random(64);

        // Store token in database
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // Send email
        $user->notify(new \App\Notifications\ResetPasswordNotification($token));

        // Set cache
        Cache::put($cacheKey, Carbon::now(), $delayMinutes * 60);

        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent to your email'
        ]);
    }

    public function resetPasswordWithToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get token from database
        $resetRecord = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token'
            ], 400);
        }

        // Check if token matches
        if (!Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reset token'
            ], 400);
        }

        // Check if token is expired (60 minutes)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Reset token has expired'
            ], 400);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete used token
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully'
        ]);
    }
}
