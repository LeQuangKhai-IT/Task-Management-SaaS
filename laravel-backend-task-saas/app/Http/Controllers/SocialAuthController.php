<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class SocialAuthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/{provider}/redirect",
     *     summary="Redirect to provider login page",
     *     description="Supported providers: google, github, slack",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Authentication provider (google|github|slack)",
     *         @OA\Schema(type="string", enum={"google", "github", "slack"})
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to provider login page",
     *         @OA\Header(
     *             header="Location",
     *             description="URL of provider login page",
     *             @OA\Schema(type="string", example="https://accounts.google.com/o/oauth2/auth?..."),
     *         )
     *     )
     * )
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->stateless()->user();
    }

    /**
     * @OA\Get(
     *     path="/api/{provider}/callback",
     *     summary="Handle provider callback",
     *     description="After login via Google/GitHub/Slack, backend will create user and return JWT token",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Authentication provider (google|github|slack)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login success",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login success"),
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOi..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="01993906-c75c-72a0-b3fd-db5da877f928"),
     *                 @OA\Property(property="fullname", type="string", example="Lê Quang Khải"),
     *                 @OA\Property(property="email", type="string", example="leequangkhai621@gmail.com"),
     *                 @OA\Property(property="avatar_url", type="string", example="https://lh3.googleusercontent.com/..."),
     *                 @OA\Property(property="provider", type="string", example="google"),
     *                 @OA\Property(property="provider_id", type="string", example="112233445566778899")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized or invalid provider"
     *     )
     * )
     */
    public function callback($provider)
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();

        // Find or create user
        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'fullname' => $socialUser->getName(),
                'avatar_url' => $socialUser->getAvatar(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]
        );

        // Create JWT for user
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login success',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
}
