<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RotateSessionOnLogin
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
        // Check if user is authenticated and session needs rotation
        if (Auth::check() && !$request->session()->has('session_rotated')) {
            // Regenerate session ID for security
            $request->session()->regenerate();
            
            // Mark session as rotated to prevent multiple regenerations
            $request->session()->put('session_rotated', true);
            
            // Log session rotation for security auditing
            Log::info('Session ID rotated for authenticated user', [
                'user_id' => Auth::id(),
                'username' => Auth::user()->username ?? Auth::user()->email,
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'timestamp' => now()->toISOString()
            ]);
        }

        return $next($request);
    }
}