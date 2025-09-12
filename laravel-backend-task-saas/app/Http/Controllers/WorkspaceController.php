<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreWorkspaceRequest;
use App\Http\Requests\UpdateWorkspaceRequest;
use App\Models\Workspace;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/workspaces",
     *     summary="Get all workspaces",
     *     tags={"Workspaces"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Workspaces retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="workspaces", type="array",
     *                     @OA\Items(ref="#/components/schemas/Workspace")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=400, description="Could not retrieve workspaces")
     * )
     */
    public function index()
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $workspaces = Workspace::all();

            return ApiResponse::success([
                'workspaces' => $workspaces,
            ], 'Workspaces retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve workspaces', 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/workspaces",
     *     summary="Create a new workspace",
     *     tags={"Workspaces"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Workspace")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Workspace created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="workspace", ref="#/components/schemas/Workspace")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function store(StoreWorkspaceRequest $request)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $validatedData = $request->only('name', 'description', 'visibility');

            $workspace = Workspace::create([
                'id' => Str::uuid()->toString(),
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'visibility' => $validatedData['visibility']
            ]);

            return ApiResponse::success([
                'workspace' => $workspace,
            ], 'Workspace created successfully', 201);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not create workspace', 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/workspaces/{id}",
     *     summary="Get a workspace by ID",
     *     tags={"Workspaces"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Workspace UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Workspace retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="workspace", ref="#/components/schemas/Workspace")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Workspace not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $workspace = Workspace::find($id);

            if (!$workspace) {
                return ApiResponse::error('Workspace not found', 404);
            }

            return ApiResponse::success([
                'workspace' => $workspace,
            ], 'Workspace retrieved successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not retrieve workspace', 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/workspaces/{id}",
     *     summary="Update a workspace",
     *     tags={"Workspaces"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Workspace UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Workspace")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Workspace updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="workspace", ref="#/components/schemas/Workspace")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Workspace not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(UpdateWorkspaceRequest $request, string $id)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $workspace = Workspace::find($id);

            if (!$workspace) {
                return ApiResponse::error('Workspace not found', 404);
            }

            $validatedData = $request->only('name', 'description', 'visibility');

            $workspace->update($validatedData);

            return ApiResponse::success([
                'workspace' => $workspace,
            ], 'Workspace updated successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not update workspace', 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/workspaces/{id}",
     *     summary="Delete a workspace",
     *     tags={"Workspaces"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Workspace UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Workspace deleted successfully"),
     *     @OA\Response(response=404, description="Workspace not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $workspace = Workspace::find($id);

            if (!$workspace) {
                return ApiResponse::error('Workspace not found', 404);
            }

            $workspace->delete();

            return ApiResponse::success(null, 'Workspace deleted successfully', 200);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::error('Invalid token', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::error('Could not delete workspace', 400);
        }
    }
}
