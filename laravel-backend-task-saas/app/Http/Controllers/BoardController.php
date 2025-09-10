<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Models\Board;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class BoardController extends Controller
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
                'workspace_id' => 'sometimes|uuid|exists:workspaces,id',
            ]);

            $query = Board::query();

            if (isset($validatedData['workspace_id'])) {
                $query->where('workspace_id', $validatedData['workspace_id']);
            }

            $boards = $query->get();

            return ApiResponse::success([
                'boards' => $boards,
            ], 'Boards retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve boards', 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBoardRequest $request)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $validatedData = $request->validate();

            $board = Board::create([
                'id' => Str::uuid()->toString(),
                'workspace_id' => $validatedData['workspace_id'],
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'visibility' => $validatedData['visibility']
            ]);

            return ApiResponse::success([
                'board' => $board,
            ], 'Board created successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not create board', 400);
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

            $board = Board::find($id);

            if (!$board) {
                return ApiResponse::error('Board not found', 404);
            }

            return ApiResponse::success([
                'board' => $board,
            ], 'Board retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve board', 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBoardRequest $request, string $id)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $board = Board::find($id);

            if (!$board) {
                return ApiResponse::error('Board not found', 404);
            }

            $validatedData = $request->validate();

            $board->update($validatedData);

            return ApiResponse::success([
                'board' => $board,
            ], 'Board updated successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not update board', 400);
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

            $board = Board::find($id);

            if (!$board) {
                return ApiResponse::error('Board not found', 404);
            }

            $board->delete();

            return ApiResponse::success(null, 'Board deleted successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not delete board', 400);
        }
    }
}
