<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SecurityMonitoringService
{
    /**
     * Monitor and log security events
     */
    public function monitorSecurityEvent(string $eventType, Request $request, array $data = []): void
    {
        $config = config('security.monitoring.security_events');
        
        if (!$config['enabled']) {
            return;
        }

        $eventData = [
            'event_type' => $eventType,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => Auth::id(),
            'timestamp' => now()->toISOString(),
            'data' => $data,
        ];

        // Log the event
        Log::channel('security')->info("Security Event: {$eventType}", $eventData);

        // Check for intrusion patterns
        $this->checkIntrusionPatterns($eventType, $request, $eventData);

        // Send alerts if necessary
        $this->checkAlertThresholds($eventType, $request, $eventData);
    }

    /**
     * Check for intrusion patterns
     */
    protected function checkIntrusionPatterns(string $eventType, Request $request, array $eventData): void
    {
        $config = config('security.monitoring.intrusion_detection');
        
        if (!$config['enabled']) {
            return;
        }

        $ip = $request->ip();
        $key = "intrusion_attempts:{$ip}";
        
        // Get current attempt count
        $attempts = Cache::get($key, 0);
        
        // Increment attempts for suspicious events
        if ($this->isSuspiciousEvent($eventType)) {
            $attempts++;
            Cache::put($key, $attempts, now()->addSeconds($config['time_window']));
            
            // Check if threshold exceeded
            if ($attempts >= $config['threshold']) {
                $this->handleIntrusionDetected($ip, $attempts, $eventData);
            }
        }
    }

    /**
     * Check if event is suspicious
     */
    protected function isSuspiciousEvent(string $eventType): bool
    {
        $suspiciousEvents = [
            'failed_login',
            'suspicious_activity',
            'access_denied',
            'rate_limit_exceeded',
            'invalid_token',
            'unauthorized_access',
        ];
        
        return in_array($eventType, $suspiciousEvents);
    }

    /**
     * Handle intrusion detected
     */
    protected function handleIntrusionDetected(string $ip, int $attempts, array $eventData): void
    {
        Log::channel('security')->critical('Intrusion detected', [
            'ip_address' => $ip,
            'attempts' => $attempts,
            'event_data' => $eventData,
            'timestamp' => now()->toISOString(),
        ]);

        // Block IP temporarily
        $this->blockIpTemporarily($ip, 3600); // Block for 1 hour

        // Send alert email
        $this->sendIntrusionAlert($ip, $attempts, $eventData);
    }

    /**
     * Block IP temporarily
     */
    protected function blockIpTemporarily(string $ip, int $seconds): void
    {
        $key = "blocked_ip:{$ip}";
        Cache::put($key, true, now()->addSeconds($seconds));
        
        Log::channel('security')->warning('IP temporarily blocked', [
            'ip_address' => $ip,
            'blocked_until' => now()->addSeconds($seconds)->toISOString(),
        ]);
    }

    /**
     * Send intrusion alert
     */
    protected function sendIntrusionAlert(string $ip, int $attempts, array $eventData): void
    {
        $config = config('security.monitoring.notifications');
        
        if (!$config['enable_email_alerts']) {
            return;
        }

        try {
            Mail::raw("Intrusion Alert\n\nIP: {$ip}\nAttempts: {$attempts}\nTime: " . now()->toISOString(), function ($message) use ($config) {
                $message->to($config['admin_email'])
                        ->subject('Security Alert: Intrusion Detected');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send intrusion alert email', [
                'error' => $e->getMessage(),
                'ip' => $ip,
            ]);
        }
    }

    /**
     * Check alert thresholds
     */
    protected function checkAlertThresholds(string $eventType, Request $request, array $eventData): void
    {
        $config = config('security.monitoring.notifications');
        
        if (!$config['enable_email_alerts']) {
            return;
        }

        $ip = $request->ip();
        $key = "alert_count:{$eventType}:{$ip}";
        
        $count = Cache::get($key, 0);
        $count++;
        
        Cache::put($key, $count, now()->addHour());
        
        // Send alert if threshold exceeded
        if ($count >= $config['alert_threshold']) {
            $this->sendSecurityAlert($eventType, $ip, $count, $eventData);
        }
    }

    /**
     * Send security alert
     */
    protected function sendSecurityAlert(string $eventType, string $ip, int $count, array $eventData): void
    {
        $config = config('security.monitoring.notifications');
        
        try {
            $message = "Security Alert\n\nEvent Type: {$eventType}\nIP: {$ip}\nCount: {$count}\nTime: " . now()->toISOString();
            
            Mail::raw($message, function ($message) use ($config, $eventType) {
                $message->to($config['admin_email'])
                        ->subject("Security Alert: {$eventType}");
            });
        } catch (\Exception $e) {
            Log::error('Failed to send security alert email', [
                'error' => $e->getMessage(),
                'event_type' => $eventType,
                'ip' => $ip,
            ]);
        }
    }

    /**
     * Generate security report
     */
    public function generateSecurityReport(int $days = 7): array
    {
        $report = [
            'period' => "Last {$days} days",
            'generated_at' => now()->toISOString(),
            'summary' => [
                'total_events' => 0,
                'failed_logins' => 0,
                'suspicious_activities' => 0,
                'blocked_ips' => 0,
                'security_alerts' => 0,
            ],
            'top_ips' => [],
            'event_types' => [],
            'recommendations' => [],
        ];

        // This would typically query a security events database
        // For now, we'll return a basic structure
        
        return $report;
    }

    /**
     * Check if IP is blocked
     */
    public function isIpBlocked(string $ip): bool
    {
        return Cache::has("blocked_ip:{$ip}");
    }

    /**
     * Unblock IP
     */
    public function unblockIp(string $ip): void
    {
        Cache::forget("blocked_ip:{$ip}");
        
        Log::channel('security')->info('IP unblocked', [
            'ip_address' => $ip,
            'unblocked_by' => auth()->id(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get blocked IPs
     */
    public function getBlockedIps(): array
    {
        // This would typically query the cache or database for blocked IPs
        return [];
    }

    /**
     * Log admin action
     */
    public function logAdminAction(string $action, array $data = []): void
    {
        $this->monitorSecurityEvent('admin_action', request(), [
            'action' => $action,
            'admin_id' => auth()->id(),
            'data' => $data,
        ]);
    }

    /**
     * Log file access
     */
    public function logFileAccess(string $filePath, string $action = 'access'): void
    {
        $this->monitorSecurityEvent('file_access', request(), [
            'file_path' => $filePath,
            'action' => $action,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Log data export
     */
    public function logDataExport(string $exportType, int $recordCount): void
    {
        $this->monitorSecurityEvent('data_export', request(), [
            'export_type' => $exportType,
            'record_count' => $recordCount,
            'user_id' => Auth::id(),
        ]);
    }
}
