<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class WorkspaceUserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/workspaces/{workspace}/users",
     *     summary="List users in workspace",
     *     tags={"Workspace Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="workspace", in="path", required=true,
     *         description="Workspace ID", @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="List of users"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function index(Workspace $workspace)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the workspace
            if ($user->role !== 'admin' && !$user->workspaces()->where('workspaces.id', $workspace->id)->exists()) {
                return ApiResponse::error('Unauthorized access to workspace', 403);
            }

            $users = $workspace->users()->get();

            return ApiResponse::success([
                'users' => $users,
            ], 'Users in workspace retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve users', 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/workspaces/{workspace}/users",
     *     summary="Add user to workspace",
     *     tags={"Workspace Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="workspace", in="path", required=true,
     *         description="Workspace ID", @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="string", format="uuid")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User added"),
     *     @OA\Response(response=400, description="Validation or already exists")
     * )
     */
    public function store(Request $request, Workspace $workspace)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the workspace
            if ($user->role !== 'admin' && !$user->workspaces()->where('workspaces.id', $workspace->id)->exists()) {
                return ApiResponse::error('Unauthorized access to workspace', 403);
            }

            $validatedData = $request->validate([
                'user_id' => 'required|uuid|exists:users,id',
            ]);

            // Check if user is already in the workspace
            if ($workspace->users()->where('users.id', $validatedData['user_id'])->exists()) {
                return ApiResponse::error('User already in workspace', 400);
            }

            // Add user to workspace
            $workspace->users()->attach($validatedData['user_id']);

            return ApiResponse::success(null, 'User added to workspace successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not add user to workspace', 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/workspaces/{workspace}/users/{user}",
     *     summary="Remove user from workspace",
     *     tags={"Workspace Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="workspace", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="user", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="User removed"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function destroy(Workspace $workspace, User $user)
    {
        try {
            // Authenticate user with JWT token
            $authenticatedUser = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the workspace
            if ($authenticatedUser->role !== 'admin' && !$authenticatedUser->workspaces()->where('workspaces.id', $workspace->id)->exists()) {
                return ApiResponse::error('Unauthorized access to workspace', 403);
            }

            // Check if user is in the workspace
            if (!$workspace->users()->where('users.id', $user->id)->exists()) {
                return ApiResponse::error('User not found in workspace', 404);
            }

            // Prevent user from removing themselves
            if ($authenticatedUser->id === $user->id) {
                return ApiResponse::error('Cannot remove yourself from workspace', 400);
            }

            // Remove user from workspace
            $workspace->users()->detach($user->id);

            return ApiResponse::success(null, 'User removed from workspace successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not remove user from workspace', 400);
        }
    }
}
