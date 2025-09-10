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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
