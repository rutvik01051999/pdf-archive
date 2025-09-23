<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class InactiveAdminLogout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to authenticated admin users
        if (Auth::check() && $this->isAdminUser()) {
            $this->checkSessionTimeout($request);
        }

        return $next($request);
    }

    /**
     * Check if the current user is an admin (simplified - any authenticated user is considered admin)
     */
    protected function isAdminUser(): bool
    {
        $user = Auth::user();
        return $user !== null;
    }

    /**
     * Check session timeout and handle accordingly
     */
    protected function checkSessionTimeout(Request $request): void
    {
        $config = config('session_timeout.admin');
        $timeoutMinutes = $config['inactive_timeout'];
        $warningMinutes = $config['warning_time'];

        // Get last activity time from session
        $lastActivity = Session::get('last_activity');
        $now = now();

        // If no last activity recorded, set it to now
        if (!$lastActivity) {
            Session::put('last_activity', $now->toISOString());
            return;
        }

        $lastActivityTime = Carbon::parse($lastActivity);
        $inactiveMinutes = $now->diffInMinutes($lastActivityTime);

        // Check if session has expired
        if ($inactiveMinutes >= $timeoutMinutes) {
            $this->handleSessionExpired($request, $inactiveMinutes);
            return;
        }

        // Check if we should show warning
        if ($inactiveMinutes >= ($timeoutMinutes - $warningMinutes)) {
            $this->handleSessionWarning($request, $timeoutMinutes - $inactiveMinutes);
        }

        // Update last activity time
        if ($config['extend_on_activity']) {
            Session::put('last_activity', $now->toISOString());
        }
    }

    /**
     * Handle session expired
     */
    protected function handleSessionExpired(Request $request, int $inactiveMinutes): void
    {
        $user = Auth::user();

        // Log the session timeout
        if (config('session_timeout.security_features.log_timeouts')) {
            Log::info('Admin session expired due to inactivity', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'inactive_minutes' => $inactiveMinutes,
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'timestamp' => now()->toISOString()
            ]);
        }

        // Clear session data
        Session::flush();
        Auth::logout();

        // For AJAX requests, return JSON response
        if ($request->wantsJson() || $request->ajax()) {
            response()->json([
                'status' => 'timeout',
                'message' => 'Your session has expired due to inactivity. Please log in again.',
                'redirect' => route('login')
            ], 401)->send();
            exit;
        }

        // For regular requests, redirect to login with message
        redirect()->route('login')
            ->with('session_expired', true)
            ->with('message', 'Your session has expired due to inactivity. Please log in again.')
            ->send();
        exit;
    }

    /**
     * Handle session warning
     */
    protected function handleSessionWarning(Request $request, int $minutesRemaining): void
    {
        // Only show warning for AJAX requests to avoid disrupting page loads
        if ($request->wantsJson() || $request->ajax()) {
            $warningData = [
                'status' => 'warning',
                'message' => str_replace(':minutes', $minutesRemaining, config('session_timeout.warning_messages.message')),
                'minutes_remaining' => $minutesRemaining,
                'show_warning' => true
            ];

            // Add warning to response headers for JavaScript to detect
            $response = response()->json($warningData, 200);
            $response->header('X-Session-Warning', json_encode($warningData));
            return;
        }

        // For regular requests, add warning to session
        Session::flash('session_warning', [
            'message' => str_replace(':minutes', $minutesRemaining, config('session_timeout.warning_messages.message')),
            'minutes_remaining' => $minutesRemaining
        ]);
    }

    /**
     * Extend session (called via AJAX)
     */
    public function extendSession(Request $request)
    {
        if (!Auth::check() || !$this->isAdminUser()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Update last activity time
        Session::put('last_activity', now()->toISOString());

        // Log session extension
        if (config('session_timeout.security_features.log_extensions')) {
            Log::info('Admin session extended', [
                'user_id' => Auth::id(),
                'username' => Auth::user()->username,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Session extended successfully',
            'extended_at' => now()->toISOString()
        ]);
    }

    /**
     * Get session status (called via AJAX)
     */
    public function getSessionStatus(Request $request)
    {
        if (!Auth::check() || !$this->isAdminUser()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $config = config('session_timeout.admin');
        $timeoutMinutes = $config['inactive_timeout'];
        $warningMinutes = $config['warning_time'];

        $lastActivity = Session::get('last_activity');
        if (!$lastActivity) {
            return response()->json([
                'status' => 'active',
                'minutes_remaining' => $timeoutMinutes,
                'warning_threshold' => $warningMinutes
            ]);
        }

        $lastActivityTime = Carbon::parse($lastActivity);
        $inactiveMinutes = now()->diffInMinutes($lastActivityTime);
        $minutesRemaining = max(0, $timeoutMinutes - $inactiveMinutes);

        return response()->json([
            'status' => $minutesRemaining > 0 ? 'active' : 'expired',
            'minutes_remaining' => $minutesRemaining,
            'warning_threshold' => $warningMinutes,
            'last_activity' => $lastActivity,
            'inactive_minutes' => $inactiveMinutes
        ]);
    }
}