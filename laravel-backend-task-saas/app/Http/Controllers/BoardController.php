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
     * @OA\Get(
     *     path="/api/boards",
     *     summary="Get all boards (optionally filtered by workspace)",
     *     tags={"Boards"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="workspace_id",
     *         in="query",
     *         description="Filter boards by workspace UUID",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Boards retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=400, description="Could not retrieve boards")
     * )
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
     * @OA\Post(
     *     path="/api/boards",
     *     summary="Create a new board",
     *     tags={"Boards"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"workspace_id","title","description","visibility"},
     *             @OA\Property(property="title", type="string", maxLength=255, description="Board title"),
     *             @OA\Property(property="description", type="string", description="Board description"),
     *             @OA\Property(property="workspace_id", type="string", format="uuid", description="Workspace UUID"),
     *             @OA\Property(property="background", type="string", maxLength=255, description="Background code color or url"),
     *             @OA\Property(property="visibility", type="string", enum={"private","workspace","public"}, description="Board visibility")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Board created successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=400, description="Could not create board")
     * )
     */
    public function store(StoreBoardRequest $request)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $validatedData = $request->only('title', 'description', 'workspace_id',  'visibility');

            $board = Board::create([
                'id' => Str::uuid()->toString(),
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'workspace_id' => $validatedData['workspace_id'],
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
     * @OA\Get(
     *     path="/api/boards/{id}",
     *     summary="Get a specific board by ID",
     *     tags={"Boards"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Board UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Board retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Board not found"),
     *     @OA\Response(response=400, description="Could not retrieve board")
     * )
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
     * @OA\Put(
     *     path="/api/boards/{id}",
     *     summary="Update a board by ID",
     *     tags={"Boards"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Board UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=255, description="Board title"),
     *             @OA\Property(property="description", type="string", description="Board description"),
     *             @OA\Property(property="workspace_id", type="string", format="uuid", description="Workspace UUID"),
     *             @OA\Property(property="background", type="string", maxLength=255, description="Background code color or url"),
     *             @OA\Property(property="visibility", type="string", enum={"private","workspace","public"}, description="Board visibility")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Board updated successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Board not found"),
     *     @OA\Response(response=400, description="Could not update board")
     * )
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

            $validatedData = $request->only('title', 'description', 'workspace_id', 'background', 'visibility');

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
     * @OA\Delete(
     *     path="/api/boards/{id}",
     *     summary="Delete a board by ID",
     *     tags={"Boards"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Board UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Board deleted successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Board not found"),
     *     @OA\Response(response=400, description="Could not delete board")
     * )
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
