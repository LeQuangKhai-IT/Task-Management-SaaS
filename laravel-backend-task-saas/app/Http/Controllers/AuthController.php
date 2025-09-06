<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Login for user.
     */
    public function login(Request $request)
    {
        //
    }

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        //
    }

    /**
     * Refresh token for user.
     */
    public function reftresh(Request $request)
    {
        //
    }

    /**
     * logout for user.
     */
    public function logout()
    {
        //
    }

    /**
     * Get the user authentication.
     */
    public function me(Request $request)
    {
        //
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request, string $id)
    {
        //
    }

    /**
     * Get forgot password for user.
     */
    public function forgotPassword(Request $request, string $id)
    {
        //
    }

    /**
     * Get reset password for user.
     */
    public function resetPassword(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
