<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Board;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class BoardActivityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/boards/{board}/activities",
     *     summary="Get all activities of a board",
     *     tags={"Boards"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="board",
     *         in="path",
     *         required=true,
     *         description="Board UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Activities retrieved successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=401, description="Invalid or expired token"),
     *     @OA\Response(response=400, description="Could not retrieve activities")
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

            // Get all activities with associated user data
            $activities = $board->activities()->with('user')->paginate(20);

            return ApiResponse::success(['activities' => $activities], 'Activities retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve activities', 400);
        }
    }
}
