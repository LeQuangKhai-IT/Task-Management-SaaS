<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Attachment;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CardAttachmentController extends Controller
{
    // Retrieve all attachments for a card
    public function index(Card $card)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Get all attachments for the card
            $attachments = $card->attachments()->get();

            return ApiResponse::success(['attachments' => $attachments], 'Attachments retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve attachments', 400);
        }
    }

    // Create a new attachment for a card
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
            $validatedData = $request->validate([
                'file' => 'required|file|mimes:jpg,png,pdf,docx|max:2048',
                'name' => 'sometimes|string|max:255',
            ]);

            // Store the file
            $path = $request->file('file')->store('attachments', 'public');

            // Create new attachment
            $attachment = Attachment::create([
                'id' => Str::uuid()->toString(),
                'card_id' => $card->id,
                'name' => $validatedData['name'] ?? $request->file('file')->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $request->file('file')->getMimeType(),
            ]);

            return ApiResponse::success(['attachment' => $attachment], 'Attachment created successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not create attachment', 400);
        }
    }

    // Retrieve a specific attachment by ID
    public function show(Card $card, Attachment $attachment)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if attachment belongs to the card
            if ($attachment->card_id !== $card->id) {
                return ApiResponse::error('Attachment not found in card', 404);
            }

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            return ApiResponse::success(['attachment' => $attachment], 'Attachment retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve attachment', 400);
        }
    }

    // Update an existing attachment
    public function update(Request $request, Card $card, Attachment $attachment)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if attachment belongs to the card
            if ($attachment->card_id !== $card->id) {
                return ApiResponse::error('Attachment not found in card', 404);
            }

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Validate request data
            $validatedData = $request->validate(['name' => 'sometimes|string|max:255']);

            // Update attachment
            $attachment->update($validatedData);

            return ApiResponse::success(['attachment' => $attachment], 'Attachment updated successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not update attachment', 400);
        }
    }

    // Delete an attachment
    public function destroy(Request $request, Card $card, Attachment $attachment)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if attachment belongs to the card
            if ($attachment->card_id !== $card->id) {
                return ApiResponse::error('Attachment not found in card', 404);
            }

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Delete file from storage
            Storage::disk('public')->delete($attachment->path);

            // Delete attachment
            $attachment->delete();

            return ApiResponse::success(null, 'Attachment deleted successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not delete attachment', 400);
        }
    }

    // Upload a file as an attachment
    public function upload(Request $request, Card $card)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Validate request data
            $validatedData = $request->validate([
                'file' => 'required|file|mimes:jpg,png,pdf,docx|max:2048',
                'name' => 'sometimes|string|max:255',
            ]);

            // Store the file
            $path = $request->file('file')->store('attachments', 'public');

            // Create new attachment
            $attachment = Attachment::create([
                'id' => Str::uuid()->toString(),
                'card_id' => $card->id,
                'name' => $validatedData['name'] ?? $request->file('file')->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $request->file('file')->getMimeType(),
            ]);

            return ApiResponse::success(['attachment' => $attachment], 'File uploaded successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not upload file', 400);
        }
    }
}
