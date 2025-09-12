<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Card;
use App\Models\Label;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CardLabelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cards/{card}/labels",
     *     summary="Get all labels attached to a card",
     *     tags={"Card Labels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="card", in="path", required=true,
     *         description="Card ID", @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Labels retrieved"),
     *     @OA\Response(response=403, description="Unauthorized")
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

            // Get all labels attached to the card
            $labels = $card->labels()->get();

            return ApiResponse::success(['labels' => $labels], 'Labels retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve labels', 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/cards/{card}/labels",
     *     summary="Attach a label to a card",
     *     tags={"Card Labels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="card", in="path", required=true,
     *         description="Card ID", @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"label_id"},
     *             @OA\Property(property="label_id", type="string", format="uuid")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Label attached"),
     *     @OA\Response(response=400, description="Invalid or already attached"),
     *     @OA\Response(response=403, description="Unauthorized")
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
            $validatedData = $request->validate(['label_id' => 'required|uuid|exists:labels,id']);

            // Check if label belongs to the card's board
            $label = Label::find($validatedData['label_id']);
            if ($label->board_id !== $card->list->board_id) {
                return ApiResponse::error('Label not found in card\'s board', 400);
            }

            // Check if label is already attached to the card
            if ($card->labels()->where('labels.id', $validatedData['label_id'])->exists()) {
                return ApiResponse::error('Label already attached to card', 400);
            }

            // Attach label to card
            $card->labels()->attach($validatedData['label_id']);

            return ApiResponse::success(null, 'Label attached to card successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not attach label to card', 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/cards/{card}/labels/{label}",
     *     summary="Detach a label from a card",
     *     tags={"Card Labels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="card", in="path", required=true,
     *         description="Card ID", @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="label", in="path", required=true,
     *         description="Label ID", @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Label detached"),
     *     @OA\Response(response=404, description="Label not found in card/board"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function destroy(Card $card, Label $label)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the card's board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to card', 403);
            }

            // Check if label belongs to the card's board
            if ($label->board_id !== $card->list->board_id) {
                return ApiResponse::error('Label not found in card\'s board', 404);
            }

            // Check if label is attached to the card
            if (!$card->labels()->where('labels.id', $label->id)->exists()) {
                return ApiResponse::error('Label not attached to card', 404);
            }

            // Detach label from card
            $card->labels()->detach($label->id);

            return ApiResponse::success(null, 'Label detached from card successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not detach label from card', 400);
        }
    }
}
