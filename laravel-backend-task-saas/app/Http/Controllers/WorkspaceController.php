<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreWorkspaceRequest;
use App\Models\Workspace;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkspaceRequest $request)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $validatedData = $request->validate();

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
     * Display the specified resource.
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Authenticate user with JWT token
            JWTAuth::parseToken()->authenticate();

            $workspace = Workspace::find($id);

            if (!$workspace) {
                return ApiResponse::error('Workspace not found', 404);
            }

            $validatedData = $request->validate();

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
     * Remove the specified resource from storage.
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
