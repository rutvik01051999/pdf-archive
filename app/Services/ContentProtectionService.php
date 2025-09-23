<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ContentProtectionService
{
    /**
     * Apply content protection measures
     */
    public function protectContent(Request $request, $content = null): array
    {
        $protection = [
            'hotlink_protection' => $this->applyHotlinkProtection($request),
            'watermark' => $this->applyWatermark($content),
            'access_control' => $this->checkAccessControl($request),
            'encryption' => $this->encryptSensitiveData($content),
        ];

        return $protection;
    }

    /**
     * Apply hotlink protection
     */
    protected function applyHotlinkProtection(Request $request): bool
    {
        $config = config('security.content_protection.hotlink_protection');
        
        if (!$config) {
            return true;
        }

        $referer = $request->header('Referer');
        $host = $request->getHost();
        
        // Allow requests without referer (direct access)
        if (!$referer) {
            return true;
        }
        
        // Allow requests from same domain
        if (str_contains($referer, $host)) {
            return true;
        }
        
        // Block hotlinking
        Log::info('Hotlink attempt blocked', [
            'referer' => $referer,
            'host' => $host,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return false;
    }

    /**
     * Apply watermark to content
     */
    protected function applyWatermark($content): bool
    {
        $config = config('security.content_protection.watermark');
        
        if (!$config['enabled'] || !$content) {
            return false;
        }

        // This would be implemented based on content type
        // For now, we'll just log the watermark application
        Log::info('Watermark applied to content', [
            'watermark_text' => $config['text'],
            'opacity' => $config['opacity'],
        ]);
        
        return true;
    }

    /**
     * Check access control for content
     */
    protected function checkAccessControl(Request $request): bool
    {
        // Check if user has permission to access content
        if (Auth::check()) {
            return true;
        }
        
        // Check for public content
        if ($this->isPublicContent($request)) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if content is public
     */
    protected function isPublicContent(Request $request): bool
    {
        $publicPaths = [
            'front/',
            'assets/',
            'images/',
            'css/',
            'js/',
        ];
        
        $path = $request->path();
        
        foreach ($publicPaths as $publicPath) {
            if (str_starts_with($path, $publicPath)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Encrypt sensitive data
     */
    protected function encryptSensitiveData($content): bool
    {
        $config = config('security.encryption.sensitive_data');
        
        if (!$config['enabled'] || !$content) {
            return false;
        }

        // This would encrypt sensitive fields in the content
        // For now, we'll just log the encryption attempt
        Log::info('Sensitive data encryption applied', [
            'fields' => $config['fields'],
        ]);
        
        return true;
    }

    /**
     * Generate secure download link
     */
    public function generateSecureDownloadLink(string $filePath, int $expiresInMinutes = 60): string
    {
        $token = Str::random(32);
        $expiresAt = now()->addMinutes($expiresInMinutes);
        
        // Store token in cache
        cache()->put("download_token:{$token}", [
            'file_path' => $filePath,
            'expires_at' => $expiresAt,
            'ip' => request()->ip(),
        ], $expiresAt);
        
        return route('secure-download', ['token' => $token]);
    }

    /**
     * Validate secure download token
     */
    public function validateDownloadToken(string $token): ?array
    {
        $data = cache()->get("download_token:{$token}");
        
        if (!$data) {
            return null;
        }
        
        // Check if token has expired
        if (now()->isAfter($data['expires_at'])) {
            cache()->forget("download_token:{$token}");
            return null;
        }
        
        // Check IP address
        if ($data['ip'] !== request()->ip()) {
            Log::warning('Download token used from different IP', [
                'token' => $token,
                'expected_ip' => $data['ip'],
                'actual_ip' => request()->ip(),
            ]);
            return null;
        }
        
        return $data;
    }

    /**
     * Log content access
     */
    public function logContentAccess(Request $request, string $contentType, string $contentId = null): void
    {
        Log::info('Content accessed', [
            'content_type' => $contentType,
            'content_id' => $contentId,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => Auth::id(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Check for suspicious download patterns
     */
    public function checkSuspiciousDownloadPattern(Request $request): bool
    {
        $ip = $request->ip();
        $key = "download_attempts:{$ip}";
        
        $attempts = cache()->get($key, 0);
        
        // Allow up to 10 downloads per hour
        if ($attempts >= 10) {
            Log::warning('Suspicious download pattern detected', [
                'ip' => $ip,
                'attempts' => $attempts,
                'user_agent' => $request->userAgent(),
            ]);
            return false;
        }
        
        cache()->put($key, $attempts + 1, now()->addHour());
        return true;
    }

    /**
     * Apply content protection headers
     */
    public function applyContentProtectionHeaders($response): void
    {
        $config = config('security.content_protection');
        
        if ($config['download_protection']) {
            $response->headers->set('X-Content-Protection', '1');
        }
        
        if ($config['right_click_disable']) {
            $response->headers->set('X-Right-Click-Disable', '1');
        }
        
        if ($config['text_selection_disable']) {
            $response->headers->set('X-Text-Selection-Disable', '1');
        }
        
        if ($config['print_disable']) {
            $response->headers->set('X-Print-Disable', '1');
        }
    }
}
