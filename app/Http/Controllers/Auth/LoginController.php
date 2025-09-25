<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\HonoAuthService;
use App\Services\ActivityLogService;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/admin/dashboard';
    protected $honoAuthService;
    public function __construct(HonoAuthService $honoAuthService)
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
        $this->honoAuthService = $honoAuthService;
    }

    /**
     * Show the login form with centers data
     */
    public function showLoginForm()
    {
        try {
            // Fetch centers from the centers database connection (same as mypdfarchive)
            $centers = DB::connection('centers')
                ->table('matrix_report_centers')
                ->select('centercode', 'description')
                ->groupBy('centercode')
                ->orderBy('description')
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to fetch centers from centers database: ' . $e->getMessage());
            $centers = collect();
        }

        // If no centers found, provide some default centers for testing
        if ($centers->isEmpty()) {
            $centers = collect([
                (object)['centercode' => '001', 'description' => 'Default Center 1'],
                (object)['centercode' => '002', 'description' => 'Default Center 2'],
                (object)['centercode' => '003', 'description' => 'Default Center 3']
            ]);
        }

        return view('auth.login', compact('centers'));
    }

    /**
     * Handle login request with automatic authentication
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
            'center' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $emailOrUsername = trim($request->email);
        $password = trim($request->password);
        $center = trim($request->center);

        // Step 1: Try Super Admin Authentication First (database only)
        $superAdminResult = $this->trySuperAdminAuthentication($request, $emailOrUsername, $password);
        if ($superAdminResult['success']) {
            return redirect()->intended($this->redirectTo);
        }

        // Step 2: Try Admin Authentication (third-party + database) with center
        $adminResult = $this->tryAdminAuthentication($request, $emailOrUsername, $password, $center);
        if ($adminResult['success']) {
            return redirect()->intended($this->redirectTo);
        }

        // Step 3: Both failed, show error
        return $this->sendFailedLoginResponse($request, $superAdminResult, $adminResult);
    }

    /**
     * Try Super Admin authentication (disabled for now - using Hono only)
     */
    protected function trySuperAdminAuthentication(Request $request, string $emailOrUsername, string $password): array
    {
        // Skip database authentication since users table doesn't have the required structure
        // This will force the system to use Hono authentication only
        return ['success' => false, 'message' => 'Super Admin authentication disabled - using Hono authentication only'];
    }

    /**
     * Try Admin authentication (third-party + database validation) - same as mypdfarchive
     */
    protected function tryAdminAuthentication(Request $request, string $emailOrUsername, string $password, string $center): array
    {
        // Step 1: Authenticate with third-party API first
        $honoResult = $this->honoAuthService->authenticateWithHono($emailOrUsername, $password);

        if (!$honoResult['success']) {
            if (isset($honoResult['error'])) {
                Log::warning('Third-party authentication failed for admin user', [
                    'username' => $emailOrUsername,
                    'hono_response' => $honoResult
                ]);
                return ['success' => false, 'message' => 'Authentication service is currently unavailable. Please try again later.'];
            } else {
                Log::warning('Third-party authentication failed for admin user', [
                    'username' => $emailOrUsername,
                    'hono_response' => $honoResult
                ]);
                return ['success' => false, 'message' => 'Invalid credentials for Admin access.'];
            }
        }

        $honoData = $honoResult['user_data'];

        // Step 2: Use same logic as mypdfarchive - check/update login table
        $this->updateLoginTable($emailOrUsername, $center, $honoData);

        // Step 3: Check if user exists in users table (same as mypdfarchive)
        $user = User::where('username', $emailOrUsername)->first();

        if (!$user) {
            // Create user in users table (same as mypdfarchive)
            $user = User::create([
                'username' => $emailOrUsername,
                'center' => $center,
                'last_login' => now()
            ]);
        } else {
            // Update last login
            $user->update(['last_login' => now()]);
        }

        // Step 4: Login the user
        Auth::login($user, $request->filled('remember'));
        $request->session()->regenerate();
        
        // Log successful login using ActivityLogService
        ActivityLogService::logAdminLogin($request, $user, [
            'login_type' => 'hono_authentication',
            'center' => $center,
            'hono_data' => $honoData
        ]);
        
        // Keep existing Laravel log for backward compatibility
        $this->logHonoLogin($request, $user, $honoData);
        
        Log::info('Admin authentication successful - Both third-party and database validation passed', [
            'username' => $emailOrUsername,
            'user_id' => $user->id
        ]);
        
        return ['success' => true, 'message' => 'Admin login successful'];
    }

    /**
     * Update login table (same logic as mypdfarchive)
     */
    protected function updateLoginTable(string $username, string $center, array $honoData): void
    {
        // List of special users that can login without center validation (from mypdfarchive)
        $specialUsers = [
            'anigup', 'narpra2', 'bheraj', '44674', '44219', '44080', 'atutal', 'vissat1',
            'hempra1', 'muggar1', 'dhasin', 'sanmal2', 'ramgau', 'visdeo', 'sanbag1', 
            'manmah', 'amiver', 'udadan', 'sausin1', 'sarven', 'monson', 'ajacha2', 
            'mohanw', 'sungup', 'SUNGUP', 'anijai', 'ANIJAI', '53042', '53467', '54387', 
            'kapsha', 'vibsha1', 'sansin8', 'Sansin8', '13576'
        ];

        // Check if user exists in login table (same as mypdfarchive)
        if (in_array($username, $specialUsers)) {
            $login = DB::table('login')->where('uname', $username)->first();
        } else {
            $login = DB::table('login')->where('uname', $username)->where('center', $center)->first();
        }

        if ($login) {
            // User exists, update if needed
            DB::table('login')
                ->where('id', $login->id)
                ->update([
                    'full_name' => $honoData['full_name'] ?? $login->full_name ?? ''
                ]);
        } else {
            // User doesn't exist, create new user (like mypdfarchive does)
            DB::table('login')->insert([
                'uname' => $username,
                'center' => $center,
                'full_name' => $honoData['full_name'] ?? ''
            ]);
        }

        // Log the login attempt (same as mypdfarchive)
        DB::table('login_logs')->insert([
            'username' => $username,
            'center' => $center,
            'last_login' => now()
        ]);
    }

    /**
     * Create new user from Hono data (for existing users only)
     */
    protected function createUserFromHonoData(array $honoData, string $emailOrUsername): ?User
    {
        try {
            $userData = [
                'username' => $emailOrUsername,
                'email' => $honoData['email'] ?? $emailOrUsername,
                'first_name' => $honoData['first_name'] ?? '',
                'last_name' => $honoData['last_name'] ?? '',
                'mobile_number' => $honoData['mobile_number'] ?? '',
                'password' => Hash::make('default_password_' . time()), // Temporary password
                'status' => true, // Fixed: use boolean instead of string
            ];

            $user = User::create($userData);
            
            // Role assignment removed - no role system
            
            return $user;
        } catch (\Exception $e) {
            Log::error('Error creating user from Hono data', [
                'username' => $emailOrUsername,
                'hono_data' => $honoData,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Update existing user from Hono data
     */
    protected function updateUserFromHonoData(User $user, array $honoData): void
    {
        $updateData = [];
        
        if (isset($honoData['first_name'])) {
            $updateData['first_name'] = $honoData['first_name'];
        }
        
        if (isset($honoData['last_name'])) {
            $updateData['last_name'] = $honoData['last_name'];
        }
        
        if (isset($honoData['mobile_number'])) {
            $updateData['mobile_number'] = $honoData['mobile_number'];
        }
        
        if (isset($honoData['email'])) {
            $updateData['email'] = $honoData['email'];
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }
    }

    /**
     * Format time remaining in a user-friendly way
     */
    protected function formatTimeRemaining(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' second' . ($seconds !== 1 ? 's' : '');
        } elseif ($seconds < 3600) {
            $minutes = round($seconds / 60);
            return $minutes . ' minute' . ($minutes !== 1 ? 's' : '');
        } else {
            $hours = round($seconds / 3600);
            return $hours . ' hour' . ($hours !== 1 ? 's' : '');
        }
    }


    /**
     * Send failed login response with specific error messages
     */
    protected function sendFailedLoginResponse(Request $request, array $superAdminResult = null, array $adminResult = null)
    {
        $emailOrUsername = $request->input('email');
        
        // Log failed login attempt using ActivityLogService
        ActivityLogService::logFailedLogin($request, [
            'super_admin_result' => $superAdminResult,
            'admin_result' => $adminResult,
            'error_details' => [
                'super_admin_message' => $superAdminResult['message'] ?? null,
                'admin_message' => $adminResult['message'] ?? null
            ]
        ]);
        
        // Keep existing Laravel log for backward compatibility
        Log::warning('Login attempt failed', [
            'username' => $emailOrUsername,
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'super_admin_result' => $superAdminResult,
            'admin_result' => $adminResult
        ]);

        // Determine the most specific error message
        $errorMessage = 'Invalid credentials. Please check your username/email and password.';
        
        if ($superAdminResult && $adminResult) {
            // Both authentication methods failed
            if (strpos($superAdminResult['message'], 'does not have Super Admin privileges') !== false) {
                $errorMessage = 'Your account does not have Super Admin privileges.';
            } elseif (strpos($adminResult['message'], 'not registered in the system') !== false) {
                $errorMessage = 'Your account is not registered in the system. Please contact your administrator.';
            } elseif (strpos($adminResult['message'], 'does not have Admin privileges') !== false) {
                $errorMessage = 'Your account does not have Admin privileges. Please contact your administrator.';
            } elseif (strpos($adminResult['message'], 'Authentication service is currently unavailable') !== false) {
                $errorMessage = 'Authentication service is currently unavailable. Please try again later.';
            } elseif (strpos($adminResult['message'], 'Invalid credentials for Admin access') !== false) {
                $errorMessage = 'Invalid credentials for Admin access.';
            }
        }

        return redirect()->back()
            ->withInput($request->except('password'))
            ->withErrors([
                'email' => $errorMessage,
            ]);
    }

    /**
     * Log admin login
     */
    protected function logAdminLogin(Request $request, User $user): void
    {
        Log::info('Admin login successful', [
            'user_id' => $user->id,
            'username' => $user->username,
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'login_type' => 'database_only'
        ]);
    }

    /**
     * Log Hono login
     */
    protected function logHonoLogin(Request $request, User $user, array $honoData): void
    {
        Log::info('Hono login successful', [
            'user_id' => $user->id,
            'username' => $user->username,
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'login_type' => 'third_party_and_database',
            'hono_data' => $honoData
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log logout using ActivityLogService
        ActivityLogService::logAdminLogout($request, $user);
        
        // Keep existing Laravel log for backward compatibility
        Log::info('User logout', [
            'user_id' => $user->id ?? null,
            'username' => $user->username ?? null,
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent')
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}