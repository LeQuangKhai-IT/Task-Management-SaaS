<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Models\Card;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class CardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cards",
     *     summary="Get all cards (optional filter by list_id)",
     *     tags={"Cards"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="list_id", in="query", required=false,
     *         description="Filter cards by list UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Cards retrieved"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $validatedData = $request->validate([
                'list_id' => 'sometimes|uuid|exists:lists,id',
            ]);

            $query = Card::query();

            if (isset($validatedData['list_id'])) {
                $query->where('list_id', $validatedData['list_id']);
            }

            $cards = $query->get();

            return ApiResponse::success([
                'cards' => $cards,
            ], 'Cards retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve cards', 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/cards",
     *     summary="Create a new card",
     *     tags={"Cards"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"list_id","name","description","archived"},
     *             @OA\Property(property="list_id", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="due_date", type="string", format="date-time"),
     *             @OA\Property(property="archived", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Card created"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function store(StoreCardRequest $request)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $validatedData = $request->validate();

            $card = Card::create([
                'id' => Str::uuid()->toString(),
                'list_id' => $validatedData['list_id'],
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'due_date' => $validatedData['due_date'],
                'archived' =>  $validatedData['archived'],
            ]);

            return ApiResponse::success([
                'card' => $card,
            ], 'Card created successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not create card', 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/cards/{id}",
     *     summary="Get a specific card by ID",
     *     tags={"Cards"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="Card UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Card retrieved"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Card not found")
     * )
     */
    public function show(string $id)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $card = Card::find($id);

            if (!$card) {
                return ApiResponse::error('Card not found', 404);
            }

            return ApiResponse::success([
                'card' => $card,
            ], 'Card retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve card', 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/cards/{id}",
     *     summary="Update a specific card",
     *     tags={"Cards"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="Card UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="list_id", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="due_date", type="string", format="date-time"),
     *             @OA\Property(property="archived", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Card updated"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Card not found")
     * )
     */
    public function update(UpdateCardRequest $request, string $id)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $card = Card::find($id);

            if (!$card) {
                return ApiResponse::error('Card not found', 404);
            }

            $validatedData = $request->validate();

            $card->update($validatedData);

            return ApiResponse::success([
                'card' => $card,
            ], 'Card updated successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not update card', 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/cards/{id}",
     *     summary="Delete a specific card",
     *     tags={"Cards"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="Card UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Card deleted"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Card not found")
     * )
     */
    public function destroy(string $id)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $card = Card::find($id);

            if (!$card) {
                return ApiResponse::error('Card not found', 404);
            }

            $card->delete();

            return ApiResponse::success(null, 'Card deleted successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not delete card', 400);
        }
    }
}
