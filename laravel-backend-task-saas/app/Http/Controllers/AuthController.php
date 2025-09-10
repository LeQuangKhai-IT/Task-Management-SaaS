<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\AfterVerifyEmailRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\CheckEmailRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResendEmailRequest;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Login for user.
     */
    public function login(LoginRequest $request)
    {
        //Validate data
        $credentials = $request->validate();

        //Authentication and create token
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        if (is_null($user->email_verified_at)) {
            return ApiResponse::error('Email not verified. Please check your inbox.', 403);
        }

        $token = JWTAuth::attempt($credentials);

        return ApiResponse::success([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request)
    {
        //Validate data
        $validatedData = $request->validate();

        //Store user
        $user = User::create([
            'id' => Str::uuid()->toString(),
            'email' => $validatedData['email'],
            'verification_token' => Str::random(64),
        ]);

        $url = config('app.frontend_url') . '/verify-email/' . $user->verification_token;

        // Send email verify
        Mail::to($user->email)->send(new VerifyEmail($user, $url));

        return ApiResponse::success(null, 'User registered. Please check your email to verify your account.', 201);
    }

    public function complete(AfterVerifyEmailRequest $request)
    {
        $validatedData = $request->validate();

        $user = User::where('email', $validatedData['email'])->first();

        if (! $user || is_null($user->email_verified_at)) {
            return ApiResponse::error('Email not verified', 403);
        }

        $user->update([
            'fullname' => $request->fullname,
            'password' => Hash::make($request->password),
        ]);

        return ApiResponse::success('Profile completed successfully');
    }

    /**
     * Refresh token for user.
     */
    public function refresh()
    {
        try {
            // Refresh token JWT
            $newToken = JWTAuth::refresh();

            // Get user authenticated
            $user = JWTAuth::setToken($newToken)->user();

            return ApiResponse::success([
                'user' => $user,
                'token' => $newToken,
            ], 'Token refreshed successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not refresh token', 400);
        }
    }

    /**
     * Logout for user.
     */
    public function logout()
    {
        try {
            // Check verify token
            JWTAuth::parseToken()->authenticate();

            // Invalidate token JWT
            JWTAuth::invalidate();

            return ApiResponse::success(null, 'Logout successful', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not logout', 400);
        }
    }

    /**
     * Get the user authentication.
     */
    public function me(Request $request)
    {
        try {
            // Check token and get user authenticated
            $user = JWTAuth::parseToken()->authenticate();

            return ApiResponse::success([
                'user' => $user,
            ], 'User information retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve user information', 400);
        }
    }

    /**
     * Change user password.
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            // Validate input data
            $validatedData = $request->validate();

            // Check token and get user authenticated
            $user = JWTAuth::parseToken()->authenticate();

            // // Check current password
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                return ApiResponse::error('Current password is incorrect', 401);
            }

            // Update new password
            $user->password = Hash::make($validatedData['new_password']);
            $user->save();

            return ApiResponse::success(null, 'Password changed successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not change password', 400);
        }
    }

    /**
     * Get forgot password for user.
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        // Validate input data
        $validatedData = $request->validate();

        //Get user's email
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return ApiResponse::error('User not found', 404);
        }

        // Create token reset password
        $token = Str::random(60);

        // Store token in table password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $validatedData['email']],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send email with link reset password
        $resetLink = url("/api/reset-password?token=$token&email={$validatedData['email']}");
        Mail::raw("Click to reset your password: $resetLink", function ($message) use ($validatedData) {
            $message->to($validatedData['email'])->subject('Password Reset Request');
        });

        return ApiResponse::success(null, 'Password reset link sent successfully', 200);
    }

    /**
     * Get reset password for user.
     */
    public function resetPassword(Request $request, string $id)
    {
        // Validate input data
        $validatedData = $request->validate();

        // Get password reset token
        $reset = DB::table('password_reset_tokens')
            ->where('email', $validatedData['email'])
            ->first();

        if (!$reset || !Hash::check($validatedData['token'], $reset->token)) {
            return ApiResponse::error('Invalid token or email', 401);
        }

        // Get user's email
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return ApiResponse::error('User not found', 404);
        }

        $user->password = Hash::make($validatedData['password']);
        $user->save();

        // Delete password reset token
        DB::table('password_reset_tokens')->where('email', $validatedData['email'])->delete();

        return ApiResponse::success(null, 'Password reset successfully', 200);
    }

    /**
     * Check email is exits
     */
    public function checkEmail(CheckEmailRequest $request)
    {
        // Validate input data
        $validatedData = $request->validate();

        // Get user's email
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return ApiResponse::error('Email not found.', 404);
        }

        return ApiResponse::success(null, 'Email already registered.', 200);
    }

    /**
     * Verify email
     */
    public function verifyEmail($token)
    {
        // Validate token
        $user = User::where('verification_token', $token)->first();

        if (! $user) {
            return redirect(config('app.frontend_url') . '/verify-failed');
        }

        $user->update([
            'email_verified_at'  => now(),
            'verification_token' => null,
        ]);

        return redirect(config('app.frontend_url') . '/complete-profile?email=' . urlencode($user->email));
    }

    /**
     * Resend email
     */
    public function resendEmail(ResendEmailRequest $request)
    {
        // Validate input data
        $validatedData = $request->validate();

        $user = User::where('email', $validatedData['email'])->first();

        if ($user->email_verified_at) {
            return ApiResponse::error('This email has already been verified.');
        }

        // generate a new token to prevent reusing the old link
        $user->update([
            'verification_token' => Str::random(64),
        ]);

        $url = config('app.frontend_url') . '/verify-email/' . $user->verification_token;

        Mail::to($user->email)->send(new VerifyEmail($user, $url));

        return ApiResponse::success('Verification email resent successfully.');
    }
}
