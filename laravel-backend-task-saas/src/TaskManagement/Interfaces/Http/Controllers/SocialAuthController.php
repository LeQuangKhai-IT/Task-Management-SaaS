<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\SocialAuthRequest;
use App\Models\User;

class SocialAuthController extends Controller
{
    /**
     * Handle the incoming request.
     */

    /*
    public function login(SocialAuthRequest $request)
    {
        // Validate input data
        $validateData = $request->only('provider', 'token');

        switch ($validateData['provider']) {
            case 'google':
                $payload = $this->verifyGoogleToken($validateData['token']);
                $email = $payload['email'] ?? null;
                $name = $payload['name'] ?? null;
                break;

            case 'github':
                $userData = $this->fetchGitHubUser($validateData['token']);
                $email = $userData['email'] ?? null;
                $name = $userData['name'] ?? $userData['login'] ?? null;
                break;

            case 'slack':
                $userData = $this->fetchSlackUser($validateData['token']);
                $email = $userData['user']['email'] ?? null;
                $name = $userData['user']['name'] ?? null;
                break;

            default:
                return ApiResponse::error('Unsupported provider');
        }

        if (!$email) {
            return ApiResponse::error('Failed to fetch user email');
        }

        // Find or create new user
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name ?? 'Unknown', 'password' => null]
        );

        // Tạo Sanctum token (hoặc JWT)
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    private function verifyGoogleToken($idToken)
    {
        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        return $client->verifyIdToken($idToken);
    }

    private function fetchGitHubUser($accessToken)
    {
        $response = \Http::withHeaders([
            'Authorization' => "token {$accessToken}",
            'Accept' => 'application/json',
        ])->get('https://api.github.com/user');

        return $response->json();
    }

    private function fetchSlackUser($accessToken)
    {
        $response = \Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
        ])->get('https://slack.com/api/users.identity');

        return $response->json();
    }
        */
}
