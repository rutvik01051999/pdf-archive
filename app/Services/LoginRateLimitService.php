<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Carbon\Carbon;

class LoginRateLimitService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('login_rate_limit');
    }

    /**
     * Check if login attempt is allowed
     */
    public function isLoginAllowed(Request $request, ?string $emailOrUsername = null): array
    {
        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');

        // Check IP-based rate limiting
        $ipCheck = $this->checkIpRateLimit($ip);
        if (!$ipCheck['allowed']) {
            return [
                'allowed' => false,
                'type' => 'ip_rate_limit',
                'message' => $ipCheck['message'],
                'retry_after' => $ipCheck['retry_after']
            ];
        }

        // Check IP-based lockout
        $ipLockoutCheck = $this->checkIpLockout($ip);
        if (!$ipLockoutCheck['allowed']) {
            return [
                'allowed' => false,
                'type' => 'ip_lockout',
                'message' => $ipLockoutCheck['message'],
                'retry_after' => $ipLockoutCheck['retry_after']
            ];
        }

        // Check user-based lockout if email/username provided
        if ($emailOrUsername && $this->config['account_lockout']['per_user']) {
            $userLockoutCheck = $this->checkUserLockout($emailOrUsername);
            if (!$userLockoutCheck['allowed']) {
                return [
                    'allowed' => false,
                    'type' => 'user_lockout',
                    'message' => $userLockoutCheck['message'],
                    'retry_after' => $userLockoutCheck['retry_after']
                ];
            }
        }

        return ['allowed' => true];
    }

    /**
     * Record a failed login attempt
     */
    public function recordFailedAttempt(Request $request, ?string $emailOrUsername = null): void
    {
        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');

        // Record IP-based attempt
        $this->recordIpAttempt($ip, $userAgent);

        // Record user-based attempt if email/username provided
        if ($emailOrUsername && $this->config['account_lockout']['per_user']) {
            $this->recordUserAttempt($emailOrUsername, $ip, $userAgent);
        }

        // Log the failed attempt
        if ($this->config['security_features']['log_failed_attempts']) {
            Log::warning('Failed login attempt', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'email_or_username' => $emailOrUsername,
                'timestamp' => now()->toISOString()
            ]);
        }

        // Check if we need to send alerts
        $this->checkAndSendAlerts($emailOrUsername, $ip);
    }

    /**
     * Record a successful login attempt
     */
    public function recordSuccessfulLogin(Request $request, User $user): void
    {
        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');

        // Clear any existing lockouts for this user and IP
        $this->clearUserLockout($user->email);
        $this->clearUserLockout($user->username);
        $this->clearIpLockout($ip);

        // Log successful login
        if ($this->config['security_features']['log_successful_logins']) {
            Log::info('Successful admin login', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'timestamp' => now()->toISOString()
            ]);
        }
    }

    /**
     * Check IP-based rate limiting
     */
    protected function checkIpRateLimit(string $ip): array
    {
        $attempts = $this->config['attempts'];
        $prefix = $this->config['cache_prefix']['login_attempts'];

        // Check per-minute limit
        $minuteKey = "{$prefix}_minute:{$ip}:" . now()->format('Y-m-d-H-i');
        $minuteCount = Cache::get($minuteKey, 0);
        if ($minuteCount >= $attempts['per_minute']) {
            return [
                'allowed' => false,
                'message' => str_replace(':time', '1 minute', $this->config['error_messages']['too_many_attempts']),
                'retry_after' => 60
            ];
        }

        // Check per-hour limit
        $hourKey = "{$prefix}_hour:{$ip}:" . now()->format('Y-m-d-H');
        $hourCount = Cache::get($hourKey, 0);
        if ($hourCount >= $attempts['per_hour']) {
            return [
                'allowed' => false,
                'message' => str_replace(':time', '1 hour', $this->config['error_messages']['too_many_attempts']),
                'retry_after' => 3600
            ];
        }

        // Check per-day limit
        $dayKey = "{$prefix}_day:{$ip}:" . now()->format('Y-m-d');
        $dayCount = Cache::get($dayKey, 0);
        if ($dayCount >= $attempts['per_day']) {
            return [
                'allowed' => false,
                'message' => str_replace(':time', '24 hours', $this->config['error_messages']['too_many_attempts']),
                'retry_after' => 86400
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Check IP-based lockout
     */
    protected function checkIpLockout(string $ip): array
    {
        $lockoutKey = $this->config['cache_prefix']['ip_lockout'] . ":{$ip}";
        $lockoutData = Cache::get($lockoutKey);

        if ($lockoutData) {
            $lockoutUntil = Carbon::parse($lockoutData['locked_until']);
            if ($lockoutUntil->isFuture()) {
                $secondsRemaining = round(now()->diffInSeconds($lockoutUntil));
                $timeFormatted = $this->formatTimeRemaining($secondsRemaining);
                return [
                    'allowed' => false,
                    'message' => str_replace(':time', $timeFormatted, $this->config['error_messages']['ip_locked']),
                    'retry_after' => $secondsRemaining
                ];
            }
        }

        return ['allowed' => true];
    }

    /**
     * Check user-based lockout
     */
    protected function checkUserLockout(string $emailOrUsername): array
    {
        $lockoutKey = $this->config['cache_prefix']['account_lockout'] . ":{$emailOrUsername}";
        $lockoutData = Cache::get($lockoutKey);

        if ($lockoutData) {
            $lockoutUntil = Carbon::parse($lockoutData['locked_until']);
            if ($lockoutUntil->isFuture()) {
                $secondsRemaining = round(now()->diffInSeconds($lockoutUntil));
                $timeFormatted = $this->formatTimeRemaining($secondsRemaining);
                return [
                    'allowed' => false,
                    'message' => str_replace(':time', $timeFormatted, $this->config['error_messages']['account_locked']),
                    'retry_after' => $secondsRemaining
                ];
            }
        }

        return ['allowed' => true];
    }

    /**
     * Record IP-based attempt
     */
    protected function recordIpAttempt(string $ip, string $userAgent): void
    {
        $prefix = $this->config['cache_prefix']['login_attempts'];

        // Increment counters
        $minuteKey = "{$prefix}_minute:{$ip}:" . now()->format('Y-m-d-H-i');
        $hourKey = "{$prefix}_hour:{$ip}:" . now()->format('Y-m-d-H');
        $dayKey = "{$prefix}_day:{$ip}:" . now()->format('Y-m-d');

        Cache::increment($minuteKey);
        Cache::increment($hourKey);
        Cache::increment($dayKey);

        // Set expiration times
        Cache::put($minuteKey, Cache::get($minuteKey), now()->addMinute());
        Cache::put($hourKey, Cache::get($hourKey), now()->addHour());
        Cache::put($dayKey, Cache::get($dayKey), now()->addDay());

        // Check if IP should be locked out
        $this->checkIpLockoutThreshold($ip);
    }

    /**
     * Record user-based attempt
     */
    protected function recordUserAttempt(string $emailOrUsername, string $ip, string $userAgent): void
    {
        $attemptKey = $this->config['cache_prefix']['account_lockout'] . "_attempts:{$emailOrUsername}";
        $attempts = Cache::get($attemptKey, 0) + 1;
        
        Cache::put($attemptKey, $attempts, now()->addMinutes($this->config['account_lockout']['lockout_duration']));

        // Check if user should be locked out
        if ($attempts >= $this->config['account_lockout']['max_attempts']) {
            $this->lockoutUser($emailOrUsername, $ip, $userAgent);
        }
    }

    /**
     * Check IP lockout threshold
     */
    protected function checkIpLockoutThreshold(string $ip): void
    {
        $attempts = $this->config['attempts'];
        $prefix = $this->config['cache_prefix']['login_attempts'];

        // Check if IP has exceeded limits and should be locked out
        $hourKey = "{$prefix}_hour:{$ip}:" . now()->format('Y-m-d-H');
        $hourCount = Cache::get($hourKey, 0);

        if ($hourCount >= $attempts['per_hour']) {
            $this->lockoutIp($ip);
        }
    }

    /**
     * Lockout user account
     */
    protected function lockoutUser(string $emailOrUsername, string $ip, string $userAgent): void
    {
        $lockoutKey = $this->config['cache_prefix']['account_lockout'] . ":{$emailOrUsername}";
        $lockoutUntil = now()->addMinutes($this->config['account_lockout']['lockout_duration']);

        Cache::put($lockoutKey, [
            'locked_at' => now()->toISOString(),
            'locked_until' => $lockoutUntil->toISOString(),
            'ip' => $ip,
            'user_agent' => $userAgent,
            'reason' => 'too_many_failed_attempts'
        ], $lockoutUntil);

        // Log the lockout
        if ($this->config['security_features']['log_lockouts']) {
            Log::warning('User account locked due to failed login attempts', [
                'email_or_username' => $emailOrUsername,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'locked_until' => $lockoutUntil->toISOString()
            ]);
        }
    }

    /**
     * Lockout IP address
     */
    protected function lockoutIp(string $ip): void
    {
        $lockoutKey = $this->config['cache_prefix']['ip_lockout'] . ":{$ip}";
        $lockoutUntil = now()->addMinutes($this->config['account_lockout']['lockout_duration']);

        Cache::put($lockoutKey, [
            'locked_at' => now()->toISOString(),
            'locked_until' => $lockoutUntil->toISOString(),
            'reason' => 'too_many_failed_attempts'
        ], $lockoutUntil);

        // Log the IP lockout
        if ($this->config['security_features']['log_lockouts']) {
            Log::warning('IP address locked due to failed login attempts', [
                'ip' => $ip,
                'locked_until' => $lockoutUntil->toISOString()
            ]);
        }
    }

    /**
     * Clear user lockout
     */
    protected function clearUserLockout(string $emailOrUsername): void
    {
        $lockoutKey = $this->config['cache_prefix']['account_lockout'] . ":{$emailOrUsername}";
        $attemptKey = $this->config['cache_prefix']['account_lockout'] . "_attempts:{$emailOrUsername}";
        
        Cache::forget($lockoutKey);
        Cache::forget($attemptKey);
    }

    /**
     * Clear IP lockout
     */
    protected function clearIpLockout(string $ip): void
    {
        $lockoutKey = $this->config['cache_prefix']['ip_lockout'] . ":{$ip}";
        Cache::forget($lockoutKey);
    }

    /**
     * Check and send alerts if needed
     */
    protected function checkAndSendAlerts(?string $emailOrUsername, string $ip): void
    {
        if (!$this->config['notifications']['enable_email_alerts']) {
            return;
        }

        $attemptKey = $this->config['cache_prefix']['account_lockout'] . "_attempts:{$emailOrUsername}";
        $attempts = Cache::get($attemptKey, 0);

        if ($attempts >= $this->config['notifications']['alert_threshold']) {
            // Send email alert to admin
            $this->sendLockoutAlert($emailOrUsername, $ip, $attempts);
        }
    }

    /**
     * Send lockout alert email
     */
    protected function sendLockoutAlert(string $emailOrUsername, string $ip, int $attempts): void
    {
        try {
            $adminEmail = $this->config['notifications']['admin_email'];
            
            Mail::raw("
Security Alert: Multiple Failed Login Attempts

Details:
- Email/Username: {$emailOrUsername}
- IP Address: {$ip}
- Failed Attempts: {$attempts}
- Time: " . now()->toISOString() . "

Please investigate this potential security threat.

This is an automated message from the Junior Editor Admin Panel.
            ", function ($message) use ($adminEmail, $emailOrUsername) {
                $message->to($adminEmail)
                        ->subject('Security Alert: Failed Login Attempts - ' . $emailOrUsername);
            });
        } catch (\Exception $e) {
            Log::error('Failed to send lockout alert email', [
                'error' => $e->getMessage(),
                'email_or_username' => $emailOrUsername,
                'ip' => $ip
            ]);
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
     * Get lockout status for debugging
     */
    public function getLockoutStatus(string $ip, ?string $emailOrUsername = null): array
    {
        $status = [
            'ip' => $ip,
            'ip_locked' => false,
            'user_locked' => false,
            'ip_attempts' => [],
            'user_attempts' => 0
        ];

        // Check IP lockout
        $ipLockoutKey = $this->config['cache_prefix']['ip_lockout'] . ":{$ip}";
        $ipLockoutData = Cache::get($ipLockoutKey);
        if ($ipLockoutData) {
            $status['ip_locked'] = true;
            $status['ip_locked_until'] = $ipLockoutData['locked_until'];
        }

        // Check IP attempts
        $prefix = $this->config['cache_prefix']['login_attempts'];
        $status['ip_attempts'] = [
            'minute' => Cache::get("{$prefix}_minute:{$ip}:" . now()->format('Y-m-d-H-i'), 0),
            'hour' => Cache::get("{$prefix}_hour:{$ip}:" . now()->format('Y-m-d-H'), 0),
            'day' => Cache::get("{$prefix}_day:{$ip}:" . now()->format('Y-m-d'), 0)
        ];

        // Check user lockout if provided
        if ($emailOrUsername) {
            $userLockoutKey = $this->config['cache_prefix']['account_lockout'] . ":{$emailOrUsername}";
            $userLockoutData = Cache::get($userLockoutKey);
            if ($userLockoutData) {
                $status['user_locked'] = true;
                $status['user_locked_until'] = $userLockoutData['locked_until'];
            }

            $userAttemptKey = $this->config['cache_prefix']['account_lockout'] . "_attempts:{$emailOrUsername}";
            $status['user_attempts'] = Cache::get($userAttemptKey, 0);
        }

        return $status;
    }
}
