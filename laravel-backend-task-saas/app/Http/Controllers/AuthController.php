<?php

namespace App\Http\Controllers;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\AfterVerifyEmailRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\CheckEmailRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResendEmailRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use App\Mail\ResetPassword;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;

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
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", default="admin@gmail.com"),
     *             @OA\Property(property="password", type="string", default="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function login(LoginRequest $request)
    {
        // Validate data
        $credentials = $request->only('email', 'password');

        // Authentication user
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        //Create token
        $token = JWTAuth::attempt($credentials);

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
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        // Validate input data
        $validatedData = $request->only('email');

        //Get user's email
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return ApiResponse::error('User not found', 404);
        }

        // Create token reset password
        $token = Str::random(64);

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
        Mail::to($user)->send(new ResetPassword($resetLink));

        return ApiResponse::success(null, 'Password reset link has been sent to your email.');
    }

    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Reset Password",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="password", type="string", default="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Invalid token or email"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        // Validate input data
        $validatedData = $request->only('email', 'token', 'password');

        // Get password reset token
        $reset = DB::table('password_reset_tokens')
            ->where('email', $validatedData['email'])
            ->first();

        // Check password reset token
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

        return ApiResponse::success(null, 'Password reset successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register User",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", default="admin@gmail.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function register(RegisterRequest $request)
    {
        //Validate data
        $validatedData = $request->only('email');

        //Store user
        $user = User::create([
            'id' => Str::uuid()->toString(),
            'email' => $validatedData['email'],
            'verification_token' => Str::random(64),
        ]);

        $url = url("/api/verify-email?token={$user->verification_token}&email={$user->email}");

        // Send email verify
        Mail::to($user->email)->send(new VerifyEmail($user, $url));

        return ApiResponse::success(null, 'User registered. Please check your email to verify your account.');
    }

    /**
     * @OA\Post(
     *     path="/api/verify-complete",
     *     summary="Complete User Profile",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="fullname", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=403, description="Email not verified")
     * )
     */
    public function complete(AfterVerifyEmailRequest $request)
    {
        $validatedData = $request->only('email', 'fullname', 'password');

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
     * @OA\Post(
     *     path="/api/check-email",
     *     summary="Check Email Existence",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Email not found")
     * )
     */
    public function checkEmail(CheckEmailRequest $request)
    {
        // Validate input data
        $validatedData = $request->only('email');

        // Get user's email
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return ApiResponse::error('Email not found.', 404);
        }

        return ApiResponse::success(null, 'Email already registered.');
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
     *         description="Invalid verification link"
     *     )
     * )
     */
    public function verifyEmail(Request $request)
    {

        // Get user from url
        $user = User::where('email', $request->query('email'))
            ->where('verification_token', $request->query('token'))
            ->first();

        if (! $user) {
            return response()->json(['message' => 'Invalid verification link'], 400);
        }

        $user->email_verified_at = now();
        $user->verification_token = null; // clear token
        $user->save();

        return ApiResponse::success([
            'user' => $user
        ], 'Email verified successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/resend-email",
     *     summary="Resend Verification Email",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=400, description="This email has already been verified")
     * )
     */
    public function resendEmail(ResendEmailRequest $request)
    {
        // Validate input data
        $validatedData = $request->only('email');

        $user = User::where('email', $validatedData['email'])->first();

        if ($user->email_verified_at) {
            return ApiResponse::error('This email has already been verified.');
        }

        // generate a new token to prevent reusing the old link
        $user->update([
            'verification_token' => Str::random(64),
        ]);

        $url = url('api/verify-email?token=' . $user->verification_token);

        // Send verify email
        Mail::to($user, $user->email)->send(new VerifyEmail($user, $url));

        return ApiResponse::success('Verification email resent successfully.');
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
     *             @OA\Property(property="data", example={"user": {"id": 1, "email": "user@example.com"}, "token": "new_jwt_token"})
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
     *     @OA\Response(response=200, description="OK"),
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

            return ApiResponse::success(null, 'Logout successfully');
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not logout', 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Get Authenticated User",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", example={"user": {"id": 1, "email": "user@example.com"}})
     *         )
     *     ),
     *     @OA\Response(response=400, description="Could not retrieve user information"),
     *     @OA\Response(response=401, description="Invalid or expired token")
     * )
     */
    public function me()
    {
        try {
            // Check token and get user authenticated
            $user = JWTAuth::parseToken()->authenticate();

            return ApiResponse::success([
                'user' => $user,
            ], 'User information retrieved successfully');
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve user information', 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/change-password",
     *     summary="Change User Password",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="current_password", type="string"),
     *             @OA\Property(property="new_password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=400, description="Could not change password"),
     *     @OA\Response(response=401, description="Invalid or expired token or incorrect current password")
     * )
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            // Validate input data
            $validatedData = $request->only('current_password', 'new_password');

            // Check token and get user authenticated
            $user = JWTAuth::parseToken()->authenticate();

            // // Check current password
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                return ApiResponse::error('Current password is incorrect', 401);
            }

            // Update new password
            $user->password = Hash::make($validatedData['new_password']);
            $user->save();

            return ApiResponse::success(null, 'Password changed successfully');
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not change password', 400);
        }
    }
}
