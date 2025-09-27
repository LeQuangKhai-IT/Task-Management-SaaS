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
    /**
     * @OA\Get(
     *     path="/api/cards/{card}/attachments",
     *     summary="Get all attachments for a card",
     *     tags={"Card Attachments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="card", in="path", required=true,
     *         description="Card UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Attachments retrieved"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/cards/{card}/attachments/{attachment}",
     *     summary="Get a specific attachment for a card",
     *     tags={"Card Attachments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="card", in="path", required=true,
     *         description="Card UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="attachment", in="path", required=true,
     *         description="Attachment UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Attachment retrieved"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Attachment not found")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/cards/{card}/attachments",
     *     summary="Create a new attachment or upload a file for a card",
     *     tags={"Card Attachments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="card", in="path", required=true,
     *         description="Card UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(property="file", type="string", format="binary", description="File to upload (jpg, png, pdf, docx)"),
     *                 @OA\Property(property="name", type="string", description="Optional custom name for the attachment")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Attachment created/uploaded"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/cards/{card}/attachments/{attachment}",
     *     summary="Update an existing attachment",
     *     tags={"Card Attachments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="card", in="path", required=true,
     *         description="Card UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="attachment", in="path", required=true,
     *         description="Attachment UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="New attachment name")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Attachment updated"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Attachment not found")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/cards/{card}/attachments/{attachment}",
     *     summary="Delete an attachment from a card",
     *     tags={"Card Attachments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="card", in="path", required=true,
     *         description="Card UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="attachment", in="path", required=true,
     *         description="Attachment UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Attachment deleted"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Attachment not found")
     * )
     */
    public function destroy(Card $card, Attachment $attachment)
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
}
