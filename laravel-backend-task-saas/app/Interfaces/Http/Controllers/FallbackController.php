<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;

/**
 * @OA\Get(
 *     path="/{any}",
 *     summary="Fallback route",
 *     @OA\Parameter(name="any", in="path", required=false, @OA\Schema(type="string")),
 *     @OA\Response(
 *         response=404,
 *         description="Not Found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Not Found.")
 *         )
 *     )
 * )
 */
class FallbackController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        return ApiResponse::error('Not Found.', 404);
    }
}
