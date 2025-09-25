<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Apply security headers
        $this->applySecurityHeaders($response, $request);

        // Apply content protection
        $this->applyContentProtection($response, $request);

        // Log security events
        $this->logSecurityEvents($request);

        return $response;
    }

    /**
     * Apply security headers to the response
     */
    protected function applySecurityHeaders(Response $response, Request $request): void
    {
        $config = config('security.headers');

        // X-Frame-Options
        if ($config['x_frame_options']) {
            $response->headers->set('X-Frame-Options', $config['x_frame_options']);
        }

        // X-Content-Type-Options
        if ($config['x_content_type_options']) {
            $response->headers->set('X-Content-Type-Options', $config['x_content_type_options']);
        }

        // X-XSS-Protection
        if ($config['x_xss_protection']) {
            $response->headers->set('X-XSS-Protection', $config['x_xss_protection']);
        }

        // Referrer Policy
        if ($config['referrer_policy']) {
            $response->headers->set('Referrer-Policy', $config['referrer_policy']);
        }

        // Permissions Policy
        if ($config['permissions_policy']) {
            $response->headers->set('Permissions-Policy', $config['permissions_policy']);
        }

        // Strict Transport Security (HTTPS only)
        if ($config['strict_transport_security']['enabled'] && $request->isSecure()) {
            $hsts = 'max-age=' . $config['strict_transport_security']['max_age'];
            
            if ($config['strict_transport_security']['include_subdomains']) {
                $hsts .= '; includeSubDomains';
            }
            
            if ($config['strict_transport_security']['preload']) {
                $hsts .= '; preload';
            }
            
            $response->headers->set('Strict-Transport-Security', $hsts);
        }

        // Content Security Policy
        if ($config['content_security_policy']['enabled']) {
            $csp = $this->buildCSPHeader($config['content_security_policy']['directives']);
            $response->headers->set('Content-Security-Policy', $csp);
        }
    }

    /**
     * Build Content Security Policy header
     */
    protected function buildCSPHeader(array $directives): string
    {
        $csp = [];
        
        foreach ($directives as $directive => $sources) {
            if (is_array($sources)) {
                $sources = implode(' ', $sources);
            }
            $csp[] = $directive . ' ' . $sources;
        }
        
        return implode('; ', $csp);
    }

    /**
     * Apply content protection measures
     */
    protected function applyContentProtection(Response $response, Request $request): void
    {
        $config = config('security.content_protection');

        // Hotlink protection
        if ($config['hotlink_protection']) {
            $this->applyHotlinkProtection($response, $request);
        }

        // Add content protection headers
        if ($config['download_protection']) {
            $response->headers->set('X-Content-Protection', '1');
        }
    }

    /**
     * Apply hotlink protection
     */
    protected function applyHotlinkProtection(Response $response, Request $request): void
    {
        $referer = $request->header('Referer');
        $host = $request->getHost();
        
        if ($referer && !str_contains($referer, $host)) {
            // Block hotlinking for images and media files
            if ($this->isMediaRequest($request)) {
                $response->setStatusCode(403);
                $response->setContent('Hotlinking not allowed');
            }
        }
    }

    /**
     * Check if request is for media files
     */
    protected function isMediaRequest(Request $request): bool
    {
        $path = $request->path();
        $mediaExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'avi', 'mov', 'pdf'];
        
        foreach ($mediaExtensions as $ext) {
            if (str_ends_with($path, '.' . $ext)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Log security events
     */
    protected function logSecurityEvents(Request $request): void
    {
        $config = config('security.monitoring.security_events');
        
        if (!$config['enabled']) {
            return;
        }

        // Log suspicious patterns
        $this->logSuspiciousActivity($request);
        
        // Log admin access
        if ($request->is('admin/*') && Auth::check()) {
            Log::info('Admin access', [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Log suspicious activity
     */
    protected function logSuspiciousActivity(Request $request): void
    {
        // Skip suspicious activity detection for DataTable AJAX requests
        if ($this->isDataTableRequest($request)) {
            return;
        }

        $suspiciousPatterns = [
            'sql' => ['union', 'select', 'insert', 'update', 'delete', 'drop', 'create'],
            'xss' => ['<script', 'javascript:', 'onload=', 'onerror='],
            'path_traversal' => ['../', '..\\', '%2e%2e%2f', '%2e%2e%5c'],
            'command_injection' => [';', '|', '&', '`', '$'],
        ];

        $input = $request->all();
        $url = $request->fullUrl();
        
        foreach ($suspiciousPatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if ($this->containsPattern($input, $pattern) || str_contains($url, $pattern)) {
                    Log::warning('Suspicious activity detected', [
                        'type' => $type,
                        'pattern' => $pattern,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'url' => $url,
                        'input' => $input,
                        'timestamp' => now()->toISOString(),
                    ]);
                    break;
                }
            }
        }
    }

    /**
     * Check if request is a DataTable AJAX request
     */
    protected function isDataTableRequest(Request $request): bool
    {
        // Check if it's an AJAX request with DataTable parameters
        if (!$request->ajax()) {
            return false;
        }

        // Check for DataTable specific parameters
        $dataTableParams = [
            'draw', 'columns', 'order', 'start', 'length', 'search'
        ];

        foreach ($dataTableParams as $param) {
            if ($request->has($param)) {
                return true;
            }
        }

        // Check if URL contains DataTable patterns
        $url = $request->fullUrl();
        if (str_contains($url, 'columns%5B') || str_contains($url, 'order%5B')) {
            return true;
        }

        return false;
    }

    /**
     * Check if input contains suspicious pattern
     */
    protected function containsPattern($input, string $pattern): bool
    {
        if (is_array($input)) {
            foreach ($input as $value) {
                if (is_string($value) && stripos($value, $pattern) !== false) {
                    return true;
                }
            }
        } elseif (is_string($input) && stripos($input, $pattern) !== false) {
            return true;
        }
        
        return false;
    }
}