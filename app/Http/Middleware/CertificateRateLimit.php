<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class CertificateRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 5, int $decayMinutes = 1): Response
    {
        $mobile = $request->input('mobile');
        $ip = $request->ip();
        
        // Get rate limiting configuration
        $config = config('certificate_rate_limit');
        
        // Create unique keys for rate limiting
        $mobileKey = $config['cache_prefix']['otp_requests'] . '_mobile:' . $mobile;
        $ipKey = $config['cache_prefix']['ip_requests'] . '_ip:' . $ip;
        
        // Check mobile number rate limit
        if ($mobile && RateLimiter::tooManyAttempts($mobileKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($mobileKey);
            
            return response()->json([
                'status' => 0,
                'message' => str_replace(':seconds', $seconds, $config['error_messages']['retry_after']),
                'retry_after' => $seconds
            ], 429);
        }
        
        // Check IP rate limit (more restrictive)
        if (RateLimiter::tooManyAttempts($ipKey, $maxAttempts * 2)) {
            $seconds = RateLimiter::availableIn($ipKey);
            
            return response()->json([
                'status' => 0,
                'message' => $config['error_messages']['too_many_ip_requests'],
                'retry_after' => $seconds
            ], 429);
        }
        
        // Hit the rate limiters
        if ($mobile) {
            RateLimiter::hit($mobileKey, $decayMinutes * 60);
        }
        RateLimiter::hit($ipKey, $decayMinutes * 60);
        
        return $next($request);
    }
}
