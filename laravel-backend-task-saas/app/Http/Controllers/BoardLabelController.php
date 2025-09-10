<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Board;
use App\Models\Label;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class BoardLabelController extends Controller
{
    /**
     * Display a listing of the resource.
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

            // Get all labels associated with the board
            $labels = $board->labels()->get();

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
     * Store a newly created resource in storage.
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

            // Validate request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'color' => 'nullable|string|max:50',
            ]);

            // Create new label
            $label = Label::create([
                'id' => Str::uuid()->toString(),
                'board_id' => $board->id,
                'name' => $validatedData['name'],
                'color' => $validatedData['color'],
            ]);

            return ApiResponse::success(['label' => $label], 'Label created successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not create label', 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Board $board, Label $label)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if label belongs to the board
            if ($label->board_id !== $board->id) {
                return ApiResponse::error('Label not found in board', 404);
            }

            // Check if user is admin or belongs to the board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $board->id)->exists() && !$user->workspaces()->where('workspaces.id', $board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to board', 403);
            }

            return ApiResponse::success(['label' => $label], 'Label retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve label', 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Board $board, Label $label)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if label belongs to the board
            if ($label->board_id !== $board->id) {
                return ApiResponse::error('Label not found in board', 404);
            }

            // Check if user is admin or belongs to the board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $board->id)->exists() && !$user->workspaces()->where('workspaces.id', $board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to board', 403);
            }

            // Validate request data
            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'color' => 'sometimes|nullable|string|max:50',
            ]);

            // Update label
            $label->update($validatedData);

            return ApiResponse::success(['label' => $label], 'Label updated successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not update label', 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Board $board, Label $label)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if label belongs to the board
            if ($label->board_id !== $board->id) {
                return ApiResponse::error('Label not found in board', 404);
            }

            // Check if user is admin or belongs to the board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $board->id)->exists() && !$user->workspaces()->where('workspaces.id', $board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to board', 403);
            }

            // Delete label
            $label->delete();

            return ApiResponse::success(null, 'Label deleted successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not delete label', 400);
        }
    }
}
