<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Card;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CardCommentController extends Controller
{
    // Retrieve all comments for a card
    public function index(Card $card)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Get all comments for the card
            $comments = $card->comments()->with('user')->get();

            return ApiResponse::success(['comments' => $comments], 'Comments retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve comments', 400);
        }
    }

    // Create a new comment for a card
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
            $validatedData = $request->validate(['content' => 'required|string']);

            // Create new comment
            $comment = Comment::create([
                'id' => Str::uuid()->toString(),
                'card_id' => $card->id,
                'user_id' => $user->id,
                'content' => $validatedData['content'],
            ]);

            return ApiResponse::success(['comment' => $comment], 'Comment created successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not create comment', 400);
        }
    }

    // Retrieve a specific comment by ID
    public function show(Card $card, Comment $comment)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if comment belongs to the card
            if ($comment->card_id !== $card->id) {
                return ApiResponse::error('Comment not found in card', 404);
            }

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            return ApiResponse::success(['comment' => $comment], 'Comment retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve comment', 400);
        }
    }

    // Delete a comment
    public function destroy(Request $request, Card $card, Comment $comment)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if comment belongs to the card
            if ($comment->card_id !== $card->id) {
                return ApiResponse::error('Comment not found in card', 404);
            }

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Delete comment
            $comment->delete();

            return ApiResponse::success(null, 'Comment deleted successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not delete comment', 400);
        }
    }
}
