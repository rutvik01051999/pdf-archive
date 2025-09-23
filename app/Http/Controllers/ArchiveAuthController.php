<?php

namespace App\Http\Controllers;

use App\Models\ArchiveLogin;
use App\Models\ArchiveLoginLog;
use App\Models\ArchiveCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ArchiveAuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        $centers = ArchiveCenter::active()->orderBy('description')->get();
        return view('archive.auth.login', compact('centers'));
    }

    /**
     * Authenticate user via external API
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'center' => 'required|string'
        ]);

        try {
            // Authenticate with external API
            $authResult = $this->authenticateWithExternalApi($request->username, $request->password);
            
            if (!$authResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication failed. Please check your credentials.'
                ], 401);
            }

            // Create or update login record
            $login = ArchiveLogin::updateOrCreate(
                [
                    'uname' => $request->username,
                    'center' => $request->center
                ],
                [
                    'full_name' => $authResult['user_info']['full_name'] ?? '',
                    'email' => $authResult['user_info']['email'] ?? '',
                    'phone' => $authResult['user_info']['phone'] ?? '',
                    'status' => 1,
                    'last_login' => now()
                ]
            );

            // Log the login attempt
            ArchiveLoginLog::create([
                'username' => $request->username,
                'center' => $request->center,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_time' => now()
            ]);

            // Set session data
            session([
                'archive_user_id' => $login->id,
                'archive_username' => $login->uname,
                'archive_full_name' => $login->full_name,
                'archive_center' => $login->center,
                'archive_authenticated' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => route('archive.display')
            ]);

        } catch (\Exception $e) {
            Log::error('Archive authentication failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Authenticate with external API (Hono)
     */
    private function authenticateWithExternalApi($username, $password)
    {
        try {
            // Encode credentials
            $encodedUsername = base64_encode($username);
            $encodedPassword = base64_encode($password);

            // Make API request
            $response = Http::withHeaders([
                'Authorization' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJEQkNMIiwibmFtZSI6IkF1dGggQXBpIiwiaWF0IjoxfQ.BuZj--wJRr-oHcsJF8KpSA9OUld7DM5xe3RfB6ZbAu0'
            ])->post('https://rest.dbclmatrix.com/auth', [
                'uname' => $encodedUsername,
                'pass' => $encodedPassword
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] == 1) {
                    return [
                        'success' => true,
                        'user_info' => $data['user_info'] ?? []
                    ];
                }
            }

            return ['success' => false];

        } catch (\Exception $e) {
            Log::error('External API authentication error: ' . $e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        // Clear archive session data
        $request->session()->forget([
            'archive_user_id',
            'archive_username',
            'archive_full_name',
            'archive_center',
            'archive_authenticated'
        ]);

        return redirect()->route('archive.login')->with('message', 'Logged out successfully');
    }

    /**
     * Check if user is authenticated for archive
     */
    public function checkAuth()
    {
        return response()->json([
            'authenticated' => session('archive_authenticated', false),
            'user' => [
                'username' => session('archive_username'),
                'full_name' => session('archive_full_name'),
                'center' => session('archive_center')
            ]
        ]);
    }

    /**
     * Middleware to check archive authentication
     */
    public static function checkArchiveAuth(Request $request)
    {
        if (!session('archive_authenticated', false)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect()->route('archive.login');
        }
        return null;
    }

    /**
     * Get user profile
     */
    public function profile()
    {
        $login = ArchiveLogin::where('uname', session('archive_username'))
            ->where('center', session('archive_center'))
            ->first();

        $recentLogins = ArchiveLoginLog::where('username', session('archive_username'))
            ->where('center', session('archive_center'))
            ->orderBy('login_time', 'desc')
            ->limit(10)
            ->get();

        return view('archive.profile', compact('login', 'recentLogins'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20'
        ]);

        try {
            $login = ArchiveLogin::where('uname', session('archive_username'))
                ->where('center', session('archive_center'))
                ->first();

            if ($login) {
                $login->update([
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'phone' => $request->phone
                ]);

                // Update session data
                session(['archive_full_name' => $request->full_name]);

                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed'
            ], 500);
        }
    }
}