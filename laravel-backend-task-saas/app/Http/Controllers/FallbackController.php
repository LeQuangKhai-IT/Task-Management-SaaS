<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        Log::warning('Invalid API route accessed: ' . $request->path());

        return ApiResponse::error('API route not found.', 404);
    }
}
