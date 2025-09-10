<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Card;
use App\Models\User;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CardUserController extends Controller
{
    // Retrieve all users assigned to a card
    public function index(Card $card)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Get all users assigned to the card
            $users = $card->users()->get();

            return ApiResponse::success(['users' => $users], 'Users in card retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve users', 400);
        }
    }

    // Assign a user to a card
    public function store(Request $request, Card $card)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Validate request data
            $validatedData = $request->validate(['user_id' => 'required|uuid|exists:users,id']);

            // Check if user is already assigned to the card
            if ($card->users()->where('users.id', $validatedData['user_id'])->exists()) {
                return ApiResponse::error('User already assigned to card', 400);
            }

            // Attach user to card
            $card->users()->attach($validatedData['user_id']);

            return ApiResponse::success(null, 'User assigned to card successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not assign user to card', 400);
        }
    }

    // Unassign a user from a card
    public function destroy(Card $card, User $user)
    {
        try {
            // Authenticate user with JWT token
            $authenticatedUser = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the card's board/workspace
            if ($authenticatedUser->role !== 'admin' && !$authenticatedUser->boards()->where('boards.id', $card->list->board_id)->exists() && !$authenticatedUser->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Check if user is assigned to the card
            if (!$card->users()->where('users.id', $user->id)->exists()) {
                return ApiResponse::error('User not found in card', 404);
            }

            // Detach user from card
            $card->users()->detach($user->id);

            return ApiResponse::success(null, 'User unassigned from card successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not unassign user from card', 400);
        }
    }
}
