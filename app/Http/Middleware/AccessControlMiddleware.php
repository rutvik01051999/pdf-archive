<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AccessControlMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check IP whitelist/blacklist
        if (!$this->checkIpAccess($request)) {
            return $this->blockAccess($request, 'IP access denied');
        }

        // Check user agent blocking
        if (!$this->checkUserAgent($request)) {
            return $this->blockAccess($request, 'User agent blocked');
        }

        // Check rate limiting
        if (!$this->checkRateLimit($request)) {
            return $this->blockAccess($request, 'Rate limit exceeded');
        }

        // Check geo blocking (if enabled)
        if (!$this->checkGeoBlocking($request)) {
            return $this->blockAccess($request, 'Geographic access denied');
        }

        return $next($request);
    }

    /**
     * Check IP access (whitelist/blacklist)
     */
    protected function checkIpAccess(Request $request): bool
    {
        $config = config('security.access_control');
        $ip = $request->ip();

        // Check blacklist first
        if ($config['ip_blacklist']['enabled']) {
            foreach ($config['ip_blacklist']['ips'] as $blockedIp) {
                if ($this->ipMatches($ip, $blockedIp)) {
                    $this->logAccessDenied($request, 'IP blacklisted', $ip);
                    return false;
                }
            }
        }

        // Check whitelist if enabled
        if ($config['ip_whitelist']['enabled']) {
            $allowed = false;
            foreach ($config['ip_whitelist']['ips'] as $allowedIp) {
                if ($this->ipMatches($ip, $allowedIp)) {
                    $allowed = true;
                    break;
                }
            }
            
            if (!$allowed) {
                $this->logAccessDenied($request, 'IP not whitelisted', $ip);
                return false;
            }
        }

        return true;
    }

    /**
     * Check if IP matches pattern (supports wildcards and CIDR)
     */
    protected function ipMatches(string $ip, string $pattern): bool
    {
        // Exact match
        if ($ip === $pattern) {
            return true;
        }

        // Wildcard match
        if (str_contains($pattern, '*')) {
            $pattern = str_replace('*', '.*', preg_quote($pattern, '/'));
            return preg_match('/^' . $pattern . '$/', $ip);
        }

        // CIDR match
        if (str_contains($pattern, '/')) {
            return $this->ipInCidr($ip, $pattern);
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    protected function ipInCidr(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);
        
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = -1 << (32 - $mask);
            
            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }
        
        return false;
    }

    /**
     * Check user agent blocking
     */
    protected function checkUserAgent(Request $request): bool
    {
        $config = config('security.access_control.user_agent_blocking');
        
        if (!$config['enabled']) {
            return true;
        }

        $userAgent = strtolower($request->userAgent() ?? '');
        
        foreach ($config['blocked_patterns'] as $pattern) {
            if (str_contains($userAgent, strtolower($pattern))) {
                $this->logAccessDenied($request, 'User agent blocked', $userAgent);
                return false;
            }
        }

        return true;
    }

    /**
     * Check rate limiting
     */
    protected function checkRateLimit(Request $request): bool
    {
        $config = config('security.rate_limiting');
        $ip = $request->ip();
        
        // Global rate limiting
        if ($config['global']['enabled']) {
            if (!$this->checkRateLimitForType($ip, 'global', $config['global'])) {
                return false;
            }
        }

        // API rate limiting
        if ($request->is('api/*') && $config['api']['enabled']) {
            if (!$this->checkRateLimitForType($ip, 'api', $config['api'])) {
                return false;
            }
        }

        // Download rate limiting
        if ($this->isDownloadRequest($request) && $config['download']['enabled']) {
            if (!$this->checkRateLimitForType($ip, 'download', $config['download'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check rate limit for specific type
     */
    protected function checkRateLimitForType(string $ip, string $type, array $config): bool
    {
        $key = "rate_limit:{$type}:{$ip}";
        $current = Cache::get($key, 0);
        
        if ($current >= $config['max_requests']) {
            $this->logAccessDenied(request(), "Rate limit exceeded for {$type}", $ip);
            return false;
        }
        
        Cache::put($key, $current + 1, now()->addMinutes($config['decay_minutes']));
        return true;
    }

    /**
     * Check if request is for file download
     */
    protected function isDownloadRequest(Request $request): bool
    {
        $path = $request->path();
        $downloadExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
        
        foreach ($downloadExtensions as $ext) {
            if (str_ends_with($path, '.' . $ext)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check geo blocking
     */
    protected function checkGeoBlocking(Request $request): bool
    {
        $config = config('security.access_control.geo_blocking');
        
        if (!$config['enabled'] || empty($config['blocked_countries'])) {
            return true;
        }

        // This would require a GeoIP service
        // For now, we'll skip this check
        return true;
    }

    /**
     * Block access and return response
     */
    protected function blockAccess(Request $request, string $reason): Response
    {
        $this->logAccessDenied($request, $reason);
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'Your request has been blocked for security reasons.',
            ], 403);
        }
        
        return response('Access Denied', 403);
    }

    /**
     * Log access denied events
     */
    protected function logAccessDenied(Request $request, string $reason, string $details = ''): void
    {
        Log::warning('Access denied', [
            'reason' => $reason,
            'details' => $details,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}