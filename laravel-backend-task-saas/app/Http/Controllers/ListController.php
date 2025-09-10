<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreListRequest;
use App\Http\Requests\UpdateListRequest;
use App\Models\TaskList;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class ListController extends Controller
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
                'board_id' => 'sometimes|uuid|exists:boards,id',
            ]);

            $query = TaskList::query();

            if (isset($validatedData['board_id'])) {
                $query->where('board_id', $validatedData['board_id']);
            }

            $lists = $query->get();

            return ApiResponse::success([
                'lists' => $lists,
            ], 'Lists retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve lists', 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreListRequest $request)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $validatedData = $request->validate();

            $list = TaskList::create([
                'id' => Str::uuid()->toString(),
                'board_id' => $validatedData['board_id'],
                'name' => $validatedData['name'],
                'archived' => $validatedData['archived'],
                'position' => $validatedData['position'],
            ]);

            return ApiResponse::success([
                'list' => $list,
            ], 'List created successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not create list', 400);
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

            $list = TaskList::find($id);

            if (!$list) {
                return ApiResponse::error('List not found', 404);
            }

            return ApiResponse::success([
                'list' => $list,
            ], 'List retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve list', 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateListRequest $request, string $id)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $list = TaskList::find($id);

            if (!$list) {
                return ApiResponse::error('List not found', 404);
            }

            $validatedData = $request->validate();

            $list->update($validatedData);

            return ApiResponse::success([
                'list' => $list,
            ], 'List updated successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not update list', 400);
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

            $list = TaskList::find($id);

            if (!$list) {
                return ApiResponse::error('List not found', 404);
            }

            $list->delete();

            return ApiResponse::success(null, 'List deleted successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not delete list', 400);
        }
    }
}
