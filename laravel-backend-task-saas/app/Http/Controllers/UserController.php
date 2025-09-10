<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Check token and get authenticated user
            JWTAuth::parseToken()->authenticate();

            $users = User::all();

            return ApiResponse::success([
                'users' => $users,
            ], 'Users retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve users', 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        //Validate input data
        $validatedData = $request->validate();

        //Store user
        $user = User::create([
            'id' => Str::uuid()->toString(),
            'email' => $validatedData['email'],
            'fullname' => $validatedData['fullname'],
            'password' => Hash::make($validatedData['password']),
            'email_verified_at' => null,
            'avatar_url' => $validatedData['avatar_url'],
        ]);

        $token = JWTAuth::fromUser($user);

        return ApiResponse::success([
            'user' => $user,
            'token' => $token,
        ], 'User created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Check token and get authenticated user
            JWTAuth::parseToken()->authenticate();

            $user = User::find($id);

            if (!$user) {
                return ApiResponse::error('User not found', 404);
            }

            return ApiResponse::success([
                'user' => $user,
            ], 'User retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve user', 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            // Check token and get authenticated user
            JWTAuth::parseToken()->authenticate();

            $user = User::find($id);

            if (!$user) {
                return ApiResponse::error('User not found', 404);
            }

            // Validate input data
            $validatedData = $request->validate();

            $user->update($validatedData);

            return ApiResponse::success([
                'user' => $user,
            ], 'User updated successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not update user', 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Check token and get authenticated user
            JWTAuth::parseToken()->authenticate();

            $user = User::find($id);

            if (!$user) {
                return ApiResponse::error('User not found', 404);
            }

            $user->delete();

            return ApiResponse::success(null, 'User deleted successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not delete user', 400);
        }
    }
}
