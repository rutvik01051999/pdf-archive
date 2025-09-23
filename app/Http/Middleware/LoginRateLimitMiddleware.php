<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LoginRateLimitService;
use Illuminate\Support\Facades\Log;

class LoginRateLimitMiddleware
{
    protected $rateLimitService;

    public function __construct(LoginRateLimitService $rateLimitService)
    {
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply rate limiting to login requests
        if ($request->isMethod('POST') && $request->routeIs('login') || $request->path() === 'secure-login') {
            $emailOrUsername = $request->input('email');
            
            // Check if login is allowed
            $rateLimitCheck = $this->rateLimitService->isLoginAllowed($request, $emailOrUsername);
            
            if (!$rateLimitCheck['allowed']) {
                // Log the blocked attempt
                Log::warning('Login attempt blocked by rate limiting', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'email_or_username' => $emailOrUsername,
                    'block_reason' => $rateLimitCheck['type'],
                    'retry_after' => $rateLimitCheck['retry_after'] ?? null,
                    'timestamp' => now()->toISOString()
                ]);

                // Return appropriate error response
                return $this->handleRateLimitExceeded($request, $rateLimitCheck);
            }
        }

        return $next($request);
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
     * Handle rate limit exceeded response
     */
    protected function handleRateLimitExceeded(Request $request, array $rateLimitCheck)
    {
        $message = $rateLimitCheck['message'];
        $retryAfter = round($rateLimitCheck['retry_after'] ?? 60);

        // Add retry-after header
        $response = redirect()->back()
            ->withInput($request->except('password'))
            ->withErrors([
                'email' => $message
            ])
            ->with('rate_limit_exceeded', true);

        // Set retry-after header for API requests
        if ($request->wantsJson()) {
            $response = response()->json([
                'status' => 0,
                'message' => $message,
                'retry_after' => $retryAfter,
                'error_type' => $rateLimitCheck['type']
            ], 429);
        }

        return $response->header('Retry-After', $retryAfter);
    }
}