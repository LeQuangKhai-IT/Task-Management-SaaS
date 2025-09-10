<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Card;
use App\Models\Checklist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CardChecklistController extends Controller
{
    // Retrieve all checklists for a card
    public function index(Card $card)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Get all checklists for the card
            $checklists = $card->checklists()->get();

            return ApiResponse::success(['checklists' => $checklists], 'Checklists retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve checklists', 400);
        }
    }

    // Create a new checklist for a card
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
            $validatedData = $request->validate(['name' => 'required|string|max:255']);

            // Create new checklist
            $checklist = Checklist::create([
                'id' => Str::uuid()->toString(),
                'card_id' => $card->id,
                'name' => $validatedData['name'],
            ]);

            return ApiResponse::success(['checklist' => $checklist], 'Checklist created successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not create checklist', 400);
        }
    }

    // Retrieve a specific checklist by ID
    public function show(Card $card, Checklist $checklist)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if checklist belongs to the card
            if ($checklist->card_id !== $card->id) {
                return ApiResponse::error('Checklist not found in card', 404);
            }

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            return ApiResponse::success(['checklist' => $checklist], 'Checklist retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve checklist', 400);
        }
    }

    // Update an existing checklist
    public function update(Request $request, Card $card, Checklist $checklist)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if checklist belongs to the card
            if ($checklist->card_id !== $card->id) {
                return ApiResponse::error('Checklist not found in card', 404);
            }

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Validate request data
            $validatedData = $request->validate(['name' => 'sometimes|string|max:255']);

            // Update checklist
            $checklist->update($validatedData);

            return ApiResponse::success(['checklist' => $checklist], 'Checklist updated successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not update checklist', 400);
        }
    }

    // Delete a checklist
    public function destroy(Card $card, Checklist $checklist)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if checklist belongs to the card
            if ($checklist->card_id !== $card->id) {
                return ApiResponse::error('Checklist not found in card', 404);
            }

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Delete checklist
            $checklist->delete();

            return ApiResponse::success(null, 'Checklist deleted successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not delete checklist', 400);
        }
    }
}
