<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Checklist;
use App\Models\Checklist_item;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ChecklistItemController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/checklists/{checklist}/items",
     *     summary="Get all checklist items",
     *     tags={"Checklist Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="checklist", in="path", required=true,
     *         description="Checklist ID", @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Items retrieved"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function index(Checklist $checklist)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the checklist's card board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $checklist->card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $checklist->card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to checklist', 403);
            }

            // Get all items for the checklist
            $items = $checklist->items()->get();

            return ApiResponse::success(['items' => $items], 'Checklist items retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve checklist items', 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/checklists/{checklist}/items",
     *     summary="Create checklist item",
     *     tags={"Checklist Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="checklist", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="is_completed", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Item created"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function store(Request $request, Checklist $checklist)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user is admin or belongs to the checklist's card board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $checklist->card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $checklist->card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to checklist', 403);
            }

            // Validate request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'is_completed' => 'sometimes|boolean',
            ]);

            // Create new checklist item
            $item = Checklist_item::create([
                'id' => Str::uuid()->toString(),
                'checklist_id' => $checklist->id,
                'name' => $validatedData['name'],
                'is_completed' => $validatedData['is_completed'] ?? false,
            ]);

            return ApiResponse::success(['item' => $item], 'Checklist item created successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not create checklist item', 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/checklists/{checklist}/items/{item}",
     *     summary="Get checklist item by ID",
     *     tags={"Checklist Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="checklist", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="item", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Item retrieved"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Checklist $checklist, Checklist_item $item)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if item belongs to the checklist
            if ($item->checklist_id !== $checklist->id) {
                return ApiResponse::error('Checklist item not found in checklist', 404);
            }

            // Check if user is admin or belongs to the checklist's card board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $checklist->card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $checklist->card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to checklist', 403);
            }

            return ApiResponse::success(['item' => $item], 'Checklist item retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve checklist item', 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/checklists/{checklist}/items/{item}",
     *     summary="Update checklist item",
     *     tags={"Checklist Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="checklist", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="item", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="is_completed", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Item updated"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(Request $request, Checklist $checklist, Checklist_item $item)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if item belongs to the checklist
            if ($item->checklist_id !== $checklist->id) {
                return ApiResponse::error('Checklist item not found in checklist', 404);
            }

            // Check if user is admin or belongs to the checklist's card board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $checklist->card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $checklist->card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to checklist', 403);
            }

            // Validate request data
            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'is_completed' => 'sometimes|boolean',
            ]);

            // Update checklist item
            $item->update($validatedData);

            return ApiResponse::success(['item' => $item], 'Checklist item updated successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not update checklist item', 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/checklists/{checklist}/items/{item}",
     *     summary="Delete checklist item",
     *     tags={"Checklist Items"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="checklist", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="item", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Item deleted"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(Checklist $checklist, Checklist_item $item)
    {
        try {
            // Authenticate user with JWT token
            $user = JWTAuth::parseToken()->authenticate();

            // Check if item belongs to the checklist
            if ($item->checklist_id !== $checklist->id) {
                return ApiResponse::error('Checklist item not found in checklist', 404);
            }

            // Check if user is admin or belongs to the checklist's card board/workspace
            if ($user->role !== 'admin' && !$user->boards()->where('boards.id', $checklist->card->list->board_id)->exists() && !$user->workspaces()->where('workspaces.id', $checklist->card->list->board->workspace_id)->exists()) {
                return ApiResponse::error('Unauthorized access to checklist', 403);
            }

            // Delete checklist item
            $item->delete();

            return ApiResponse::success(null, 'Checklist item deleted successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not delete checklist item', 400);
        }
    }
}
