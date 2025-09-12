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
     * @OA\Get(
     *     path="/api/lists",
     *     summary="Get all lists",
     *     tags={"Lists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="board_id", in="query", required=false,
     *         description="Filter by board id",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Lists retrieved")
     * )
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
     * @OA\Post(
     *     path="/api/lists",
     *     summary="Create new list",
     *     tags={"Lists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"board_id","title","position"},
     *             @OA\Property(property="board_id", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="position", type="double")
     *         )
     *     ),
     *     @OA\Response(response=201, description="List created")
     * )
     */
    public function store(StoreListRequest $request)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $validatedData = $request->only('title', 'position', 'is_archived');

            $list = TaskList::create([
                'id' => Str::uuid()->toString(),
                'board_id' => $validatedData['board_id'],
                'title' => $validatedData['title'],
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
     * @OA\Get(
     *     path="/api/lists/{id}",
     *     summary="Get list by id",
     *     tags={"Lists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="List retrieved"),
     *     @OA\Response(response=404, description="List not found")
     * )
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
     * @OA\Put(
     *     path="/api/lists/{id}",
     *     summary="Update list",
     *     tags={"Lists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="is_archived", type="boolean"),
     *             @OA\Property(property="position", type="double")
     *         )
     *     ),
     *     @OA\Response(response=200, description="List updated"),
     *     @OA\Response(response=404, description="List not found")
     * )
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

            $validatedData = $request->only('title', 'position', 'is_archived');

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
     * @OA\Delete(
     *     path="/api/lists/{id}",
     *     summary="Delete list",
     *     tags={"Lists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="List deleted"),
     *     @OA\Response(response=404, description="List not found")
     * )
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
