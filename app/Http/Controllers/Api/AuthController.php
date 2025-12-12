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
use Illuminate\Support\Facades\DB;
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

        $this->createSession($user, $request, $token);

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

        $this->createSession($user, $request, $token);

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

        if ($currentToken) {
            $hashedToken = hash('sha256', $currentToken);
            Session::where('user_id', $user->id)
                ->where('payload', 'LIKE', '%' . $hashedToken . '%')
                ->delete();
        }

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

        if ($this->isRateLimited('email_verification_sent_' . $user->id, 1)) {
            return $this->rateLimitResponse('verification email');
        }

        $user->sendEmailVerificationNotification();
        $this->setRateLimit('email_verification_sent_' . $user->id, 1);

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

        if ($this->isRateLimited('password_reset_sent_' . $user->id, 1)) {
            return $this->rateLimitResponse('password reset email');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        $user->notify(new \App\Notifications\ResetPasswordNotification($token));
        $this->setRateLimit('password_reset_sent_' . $user->id, 1);

        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent to your email'
        ]);
    }

    public function forgotPasswordReset(Request $request)
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

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token'
            ], 400);
        }

        if (!Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reset token'
            ], 400);
        }

        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Reset token has expired'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully'
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    private function createSession(User $user, Request $request, string $token): void
    {
        Session::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => json_encode(['token' => hash('sha256', $token)]),
            'last_activity' => time(),
        ]);
    }

    private function isRateLimited(string $cacheKey, int $delayMinutes): bool
    {
        if (!Cache::has($cacheKey)) {
            return false;
        }

        $lastSent = Cache::get($cacheKey);
        $nextAllowed = Carbon::parse($lastSent)->addMinutes($delayMinutes);

        return Carbon::now()->lt($nextAllowed);
    }

    private function setRateLimit(string $cacheKey, int $delayMinutes): void
    {
        Cache::put($cacheKey, Carbon::now(), $delayMinutes * 60);
    }

    private function rateLimitResponse(string $type)
    {
        return response()->json([
            'success' => false,
            'message' => 'Please wait before requesting another ' . $type
        ], 429);
    }

    public function requestAccountDeletion(Request $request)
    {
        $user = $request->user();

        if ($this->isRateLimited('account_deletion_sent_' . $user->id, 5)) {
            return $this->rateLimitResponse('account deletion email');
        }

        $token = Str::random(64);

        DB::table('account_deletion_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        $user->notify(new \App\Notifications\DeleteAccountNotification($token));
        $this->setRateLimit('account_deletion_sent_' . $user->id, 5);

        return response()->json([
            'success' => true,
            'message' => 'Account deletion confirmation email sent'
        ]);
    }

    public function confirmAccountDeletion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $deletionRecord = DB::table('account_deletion_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$deletionRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired deletion token'
            ], 400);
        }

        if (!Hash::check($request->token, $deletionRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid deletion token'
            ], 400);
        }

        if (Carbon::parse($deletionRecord->created_at)->addMinutes(30)->isPast()) {
            DB::table('account_deletion_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Deletion token has expired'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        $user->tokens()->delete();
        Session::where('user_id', $user->id)->delete();

        DB::table('account_deletion_tokens')->where('email', $request->email)->delete();

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account has been permanently deleted'
        ]);
    }
}
