<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Board;
use App\Models\User;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class BoardUserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/boards/{board}/users",
     *     summary="Get all users in a board",
     *     tags={"Board Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="board",
     *         in="path",
     *         required=true,
     *         description="Board UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Users retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Board $board)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $board->id)->exists() && !$user->workspaces()->where('workspaces.id', $board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to board', 403);
            }

            $users = $board->users()->get();

            return ApiResponse::success([
                'users' => $users,
            ], 'Users in board retrieved successfully', 200);
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
     *     path="/api/boards/{board}/users",
     *     summary="Add a user to a board",
     *     tags={"Board Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="board",
     *         in="path",
     *         required=true,
     *         description="Board UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="string", format="uuid", description="UUID of the user to add")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User added successfully"),
     *     @OA\Response(response=400, description="User already in board or invalid input"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(Request $request, Board $board)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $board->id)->exists() && !$user->workspaces()->where('workspaces.id', $board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to board', 403);
            }

            $validatedData = $request->validate([
                'user_id' => 'required|uuid|exists:users,id',
            ]);

            // Check if user is already in the board
            if ($board->users()->where('users.id', $validatedData['user_id'])->exists()) {
                return ApiResponse::error('User already in board', 400);
            }

            // Add user to board
            $board->users()->attach($validatedData['user_id']);

            return ApiResponse::success(null, 'User added to board successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not add user to board', 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/boards/{board}/users/{user}",
     *     summary="Remove a user from a board",
     *     tags={"Board Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="board",
     *         in="path",
     *         required=true,
     *         description="Board UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="User UUID to remove",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="User removed successfully"),
     *     @OA\Response(response=400, description="Cannot remove yourself"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="User not found in board")
     * )
     */
    public function destroy(Board $board, User $user)
    {
        try {
            // Authenticate user with JWT token
            $authenticatedUser = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the board/workspace
            if ($authenticatedUser->role !== 'admin' && !$authenticatedUser->boards()->where('boards.id', $board->id)->exists() && !$authenticatedUser->workspaces()->where('workspaces.id', $board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to board', 403);
            }

            // Check if user is in the board
            if (!$board->users()->where('users.id', $user->id)->exists()) {
                return ApiResponse::error('User not found in board', 404);
            }

            // Prevent user from removing themselves
            if ($authenticatedUser->id === $user->id) {
                return ApiResponse::error('Cannot remove yourself from board', 400);
            }

            // Remove user from board
            $board->users()->detach($user->id);

            return ApiResponse::success(null, 'User removed from board successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not remove user from board', 400);
        }
    }
}
