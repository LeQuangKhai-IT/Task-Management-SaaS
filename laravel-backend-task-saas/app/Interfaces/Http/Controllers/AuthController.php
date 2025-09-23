<?php

namespace App\Http\Controllers;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\AfterVerifyEmailRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Requests\ResendVerifyEmailRequest;
use App\Mail\ResetPassword;
use App\Mail\VerifyEmail;
use App\Models\User;

/**
 * @OA\Info(
 *     title="Authentication API",
 *     version="1.0.0",
 *     description="API for user authentication and management"
 * )
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Local server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 *   )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register User",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", default="leequangkhai621@gmail.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Send verify email successfully.")
     * )
     */
    public function register(RegisterRequest $request)
    {
        // Validate input data
        $validatedData = $request->safe()->only('email');

        // Check email has been used register or not
        $user = User::withTrashed()->where('email', $validatedData['email'])->first();

        // Check account has disabled
        if ($user->trashed()) {
            return ApiResponse::error('This account is disabled.', 404);
        }

        // Check account has been register
        if ($user) {
            return ApiResponse::error('Email already registered.', 409);
        }

        // Store email address has not been verified
        $verification_token = Str::random(64);

        DB::table('email_verifications')->insert([
            'email' => $validatedData['email'],
            'verification_token' => $verification_token
        ]);

        $url = route('email.verify', [
            'token' => $verification_token,
            'email' => $validatedData['email'],
        ]);
        // Send email verify
        Mail::to($validatedData['email'])->send(new VerifyEmail($validatedData['email'], $url));

        return ApiResponse::success(null, 'Send verify email successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/resend-verify-email",
     *     summary="Resend Verification Email",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", default="leequangkhai621@gmail.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Verification email resent successfully."),
     *     @OA\Response(response=400, description="This email has not been register.")
     * )
     */
    public function resendVerifyEmail(ResendVerifyEmailRequest $request)
    {
        // Validate input data
        $validatedData = $request->safe()->only('email');

        // Check email 
        $checkEmail = DB::table('email_verifications')->where('email', $validatedData['email'])->first();

        if (!$checkEmail) {
            return ApiResponse::error('This email has not been register.');
        }

        // generate a new token to prevent reusing the old link
        $verification_token = Str::random(64);
        DB::table('email_verifications')->update(['verification_token' => $verification_token]);

        $url = route('email.verify', [
            'token' => $verification_token,
            'email' => $validatedData['email'],
        ]);

        // Send verify email
        Mail::to($validatedData['email'])->send(new VerifyEmail($validatedData['email'], $url));

        return ApiResponse::success(null, 'Verification email resent successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/verify-email",
     *     tags={"Auth"},
     *     summary="Verify user email",
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Verification token"
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="User email"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Email verification failed"
     *     )
     * )
     */
    public function verifyEmail(Request $request)
    {
        // Check email is validated when double verify
        $isVerify = DB::table('users')->where('email', $request->email)->first();

        // Check email from url
        $checkEmail = DB::table('email_verifications')
            ->where('email', $request->email)
            ->where('verification_token', $request->token)
            ->first();

        if ($isVerify || ! $checkEmail) {
            return ApiResponse::error('Token is invalid or expired.', 401);
        }

        //Store user
        User::create([
            'id' => Str::uuid()->toString(),
            'email' =>  $request->email,
            'email_verified_at' => now(),
        ]);
        return ApiResponse::success(null, 'Email verified successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/verify-complete",
     *     summary="Complete User Profile",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", default="leequangkhai621@gmail.com"),
     *             @OA\Property(property="fullname", type="string", default="Lê Quang Khải"),
     *             @OA\Property(property="password", type="string", default="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile completed successfully."),
     *     @OA\Response(response=401, description="This email has not been verify.")
     * )
     */
    public function complete(AfterVerifyEmailRequest $request)
    {
        $validatedData = $request->safe()->only('email', 'fullname', 'password');

        // Check user verify
        $user = User::all()->where('email', $validatedData['email'])->first();

        if (!$user) {
            return ApiResponse::error('This email has not been verify.', 401);
        }

        $user->update([
            'fullname' => $validatedData['fullname'],
            'password' => Hash::make($validatedData['password']),
        ]);
        $user->save();

        // Delete record email_verifications
        DB::table('email_verifications')->where('email', $validatedData['email'])->delete();

        return ApiResponse::success(
            ['user' => $user],
            'Profile completed successfully.'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", default="leequangkhai621@gmail.com"),
     *             @OA\Property(property="password", type="string", default="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successful authentication"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function login(LoginRequest $request)
    {
        // Validate data
        $validatedData = $request->safe()->only('email', 'password');

        // Get user of email
        $user = User::withTrashed()->where('email', $validatedData['email'])->first();

        // Check verify email
        if (! $user) {
            return ApiResponse::error('Email address has not been verified.', 401, 'verify');
        }

        // Check account availability
        if ($user->trashed()) {
            return ApiResponse::error('This account is disabled.', 404);
        }

        // Check password and fullname
        if (is_null($user->password) || is_null($user->fullname)) {
            return ApiResponse::error('Account setup not completed. Please finish registration.', 403);
        };

        // Authentication user
        if (! Hash::check($validatedData['password'], $user->password)) {
            return ApiResponse::error('Invalid credentials.', 401, 'unauthentication');
        }

        // Update hash version if need
        if (Hash::needsRehash($user->password)) {
            $user->password = Hash::make($validatedData['password']);
            $user->save();
        }

        //Create token
        $token = JWTAuth::attempt([$user->email, $user->password]);

        return ApiResponse::success([
            'user' => $user,
            'token' => $token,
        ], 'User login successfully.');
    }


    /**
     * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Forgot Password",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", default="leequangkhai621@gmail.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password reset link has been sent to your email"),
     *     @OA\Response(response=404, description="User not exits")
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        // Validate input data
        $validatedData = $request->safe()->only('email');

        //Get user's email
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return ApiResponse::error('User not exits.', 404);
        }

        // Create token reset password
        $token = Str::random(64);

        // Store token in table password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            [
                'userID' => $user->id,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send email with link reset password
        $resetLink = route('password.reset', [
            'userID' => $user->id,
            'token' => $token,
        ]);
        Mail::to($user)->send(new ResetPassword($resetLink));

        return ApiResponse::success(null, 'Password reset link has been sent to your email.');
    }

    /**
     * @OA\Post(
     *     path="/api/resend-forgot-password",
     *     summary="Resend Forgot Password",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", default="leequangkhai621@gmail.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Forgot password resent successfully."),
     *     @OA\Response(response=400, description="This email has not been register.")
     * )
     */
    public function resendForgotPassword(ForgotPasswordRequest $request)
    {
        // Validate input data
        $validatedData = $request->safe()->only('email');

        // Get user of email
        $user = User::where('email', $validatedData['email'])->first();

        // Check user has been register
        if (!$user) {
            return ApiResponse::error('This email has not been register.');
        }

        // Create token reset password
        $token = Str::random(64);

        // Store token in table password_reset_tokens
        DB::table('password_reset_tokens')
            ->where('userID', $user->id)
            ->update(
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );
        // Send email with link reset password
        $resetLink = route('password.reset', [
            'token' => $token,
            'userID' => $user->id,
        ]);
        Mail::to($user)->send(new ResetPassword($resetLink));

        return ApiResponse::success(null, 'Forgot password resent successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Reset Password",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="userID", type="string"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="password", type="string", default="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password reset successfully."),
     *     @OA\Response(response=401, description="Invalid token or email"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        // Validate input data
        $validatedData = $request->safe()->only('userID', 'token', 'password');

        // Get password reset token
        $reset = DB::table('password_reset_tokens')
            ->where('userID', $validatedData['userID'])
            ->first();

        // Check reset token
        if (!$reset || !Hash::check($validatedData['token'], $reset->token)) {
            return ApiResponse::error('Token is invalid or expired', 401);
        }

        // Get user's email
        $user = User::where('id', $reset->userID)->first();
        error_log($user);
        if (!$user) {
            return ApiResponse::error('User not found', 404);
        }

        // Check new password different current password
        if (Hash::check($validatedData['password'], $user->password)) {
            return ApiResponse::error('The new password have to different current password.', 401);
        }

        $user->password = Hash::make($validatedData['password']);
        $user->save();

        // Delete password reset token
        DB::table('password_reset_tokens')->where('userID', $validatedData['userID'])->delete();

        return ApiResponse::success(null, 'Password reset successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/change-password",
     *     summary="Change User Password",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="current_password", type="string", default="password"),
     *             @OA\Property(property="new_password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password changed successfully"),
     *     @OA\Response(response=400, description="Could not change password"),
     *     @OA\Response(response=401, description="Invalid or expired token or incorrect current password")
     * )
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            // Validate input data
            $validatedData = $request->safe()->only('current_password', 'new_password');

            // Check token and get user authenticated
            $user = JWTAuth::parseToken()->authenticate();

            // // Check current password
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                return ApiResponse::error('Current password is incorrect', 401);
            }

            // Update new password
            $user->password = Hash::make($validatedData['new_password']);
            $user->save();

            return ApiResponse::success(null, 'Password changed successfully.');
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not change password', 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Refresh JWT Token",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", example={"user": {"id": 1, "email": "user@gmail.com"}, "token": "new_jwt_token"})
     *         )
     *     ),
     *     @OA\Response(response=400, description="Could not refresh token"),
     *     @OA\Response(response=401, description="Invalid or expired token")
     * )
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
            ], 'Token refreshed successfully');
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not refresh token', 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout User",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Logout successfully"),
     *     @OA\Response(response=400, description="Could not logout"),
     *     @OA\Response(response=401, description="Invalid or expired token")
     * )
     */
    public function logout()
    {
        try {
            // Check verify token
            JWTAuth::parseToken()->authenticate();

            // Invalidate token JWT
            JWTAuth::invalidate();

            return ApiResponse::success(null, 'Logout successfully.');
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not logout', 400);
        }
    }
}
