<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function handleGoogleAuth(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        try {
            // Verify Google JWT token
            $response = Http::get('https://www.googleapis.com/oauth2/v3/tokeninfo', [
                'id_token' => $request->token
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google token'
                ], 400);
            }

            $googleUser = $response->json();

            // Check if user exists
            $user = User::where('email', $googleUser['email'])->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'email_verified_at' => now(),
                    'password' => bcrypt(Str::random(32)), // Random password since they'll use Google
                    'google_id' => $googleUser['sub'],
                    'avatar' => $googleUser['picture'] ?? null,
                    'login_first' => 1,
                ]);
            } else {
                // Update existing user with Google info if not already set
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser['sub'],
                        'avatar' => $googleUser['picture'] ?? $user->avatar,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);
                }
            }

            Auth::login($user);

            return response()->json([
                'success' => true,
                'user' => $user,
                'message' => 'Google authentication successful'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleGoogleCallback(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        try {
            // Exchange authorization code for access token
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'code' => $request->code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
            ]);

            if (!$tokenResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to exchange authorization code'
                ], 400);
            }

            $tokenData = $tokenResponse->json();

            // Get user info from Google
            $userResponse = Http::withToken($tokenData['access_token'])
                ->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if (!$userResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get user info from Google'
                ], 400);
            }

            $googleUser = $userResponse->json();

            // Check if user exists
            $user = User::where('email', $googleUser['email'])->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'email_verified_at' => now(),
                    'password' => bcrypt(Str::random(32)),
                    'google_id' => $googleUser['id'],
                    'avatar' => $googleUser['picture'] ?? null,
                ]);
            } else {
                // Update existing user
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser['id'],
                        'avatar' => $googleUser['picture'] ?? $user->avatar,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);
                }
            }

            Auth::login($user);

            return response()->json([
                'success' => true,
                'user' => $user,
                'message' => 'Google authentication successful'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
