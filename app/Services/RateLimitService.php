<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RateLimitService
{
    /**
     * Check if mobile number can send OTP (registration)
     */
    public static function canSendOtp(string $mobileNumber): bool
    {
        $now = now();
        
        // Check per 1 minute limit (max 1 OTP per 1 minute)
        $lastOneMinute = self::getOtpCount($mobileNumber, $now->copy()->subMinutes(1));
        if ($lastOneMinute >= 1) {
            return false;
        }
        
        // Check per hour limit (max 5 OTPs per hour)
        $lastHour = self::getOtpCount($mobileNumber, $now->copy()->subHour());
        if ($lastHour >= 5) {
            return false;
        }
        
        // Check per day limit (max 20 OTPs per day)
        $lastDay = self::getOtpCount($mobileNumber, $now->copy()->subDay());
        if ($lastDay >= 20) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if mobile number can download certificate
     */
    public static function canDownloadCertificate(string $mobileNumber): bool
    {
        $now = now();
        
        // Check per minute limit (max 1 download per minute)
        $lastMinute = self::getDownloadCount($mobileNumber, $now->copy()->subMinutes(1));
        if ($lastMinute >= 1) {
            return false;
        }
        
        // Check per hour limit (max 3 downloads per hour)
        $lastHour = self::getDownloadCount($mobileNumber, $now->copy()->subHour());
        if ($lastHour >= 3) {
            return false;
        }
        
        // Check per day limit (max 5 downloads per day)
        $lastDay = self::getDownloadCount($mobileNumber, $now->copy()->subDay());
        if ($lastDay >= 5) {
            return false;
        }
        
        return true;
    }

    /**
     * Check IP-based rate limiting
     */
    public static function canMakeRequest(string $ip, string $type = 'general'): bool
    {
        $now = now();
        $limits = self::getIpLimits($type);
        
        // Check per minute limit
        $lastMinute = self::getIpRequestCount($ip, $type, $now->copy()->subMinutes(1));
        if ($lastMinute >= $limits['per_minute']) {
            return false;
        }
        
        // Check per hour limit
        $lastHour = self::getIpRequestCount($ip, $type, $now->copy()->subHour());
        if ($lastHour >= $limits['per_hour']) {
            return false;
        }
        
        // Check per day limit
        $lastDay = self::getIpRequestCount($ip, $type, $now->copy()->subDay());
        if ($lastDay >= $limits['per_day']) {
            return false;
        }
        
        return true;
    }

    /**
     * Get rate limit information for mobile number
     */
    public static function getRateLimitInfo(string $mobileNumber, string $type = 'otp'): array
    {
        $now = now();
        
        if ($type === 'otp') {
            $lastOneMinute = self::getOtpCount($mobileNumber, $now->copy()->subMinutes(1));
            $lastHour = self::getOtpCount($mobileNumber, $now->copy()->subHour());
            $lastDay = self::getOtpCount($mobileNumber, $now->copy()->subDay());
            
            $limits = [
                'per_one_minute' => 1,
                'per_hour' => 5,
                'per_day' => 20,
            ];
        } else { // certificate download
            $lastOneMinute = self::getDownloadCount($mobileNumber, $now->copy()->subMinutes(1));
            $lastHour = self::getDownloadCount($mobileNumber, $now->copy()->subHour());
            $lastDay = self::getDownloadCount($mobileNumber, $now->copy()->subDay());
            
            $limits = [
                'per_one_minute' => 1,
                'per_hour' => 3,
                'per_day' => 5,
            ];
        }
        
        return [
            'can_proceed' => $type === 'otp' ? self::canSendOtp($mobileNumber) : self::canDownloadCertificate($mobileNumber),
            'counts' => [
                'per_one_minute' => $lastOneMinute,
                'per_hour' => $lastHour,
                'per_day' => $lastDay,
            ],
            'limits' => $limits,
            'next_available' => self::getNextAvailableTime($mobileNumber, $type)
        ];
    }

    /**
     * Get IP rate limit information
     */
    public static function getIpRateLimitInfo(string $ip, string $type = 'general'): array
    {
        $now = now();
        $limits = self::getIpLimits($type);
        
        $lastMinute = self::getIpRequestCount($ip, $type, $now->copy()->subMinutes(1));
        $lastHour = self::getIpRequestCount($ip, $type, $now->copy()->subHour());
        $lastDay = self::getIpRequestCount($ip, $type, $now->copy()->subDay());
        
        return [
            'can_proceed' => self::canMakeRequest($ip, $type),
            'counts' => [
                'per_minute' => $lastMinute,
                'per_hour' => $lastHour,
                'per_day' => $lastDay,
            ],
            'limits' => $limits,
            'next_available' => self::getIpNextAvailableTime($ip, $type)
        ];
    }

    /**
     * Record OTP request
     */
    public static function recordOtpRequest(string $mobileNumber): void
    {
        $key = "otp_requests:{$mobileNumber}:" . now()->timestamp;
        Cache::put($key, 1, now()->addDay());
        
        // Log the request
        Log::info('OTP request recorded', [
            'mobile' => $mobileNumber,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Record certificate download
     */
    public static function recordCertificateDownload(string $mobileNumber): void
    {
        $key = "cert_downloads:{$mobileNumber}:" . now()->timestamp;
        Cache::put($key, 1, now()->addDay());
        
        // Log the download
        Log::info('Certificate download recorded', [
            'mobile' => $mobileNumber,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Record IP request
     */
    public static function recordIpRequest(string $ip, string $type = 'general'): void
    {
        $key = "ip_requests:{$type}:{$ip}:" . now()->timestamp;
        Cache::put($key, 1, now()->addDay());
    }

    /**
     * Get OTP count for mobile number since given time
     */
    private static function getOtpCount(string $mobileNumber, Carbon $since): int
    {
        $pattern = "otp_requests:{$mobileNumber}:*";
        $keys = Cache::getRedis()->keys($pattern);
        
        $count = 0;
        foreach ($keys as $key) {
            $timestamp = (int) substr($key, strrpos($key, ':') + 1);
            if ($timestamp >= $since->timestamp) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Get download count for mobile number since given time
     */
    private static function getDownloadCount(string $mobileNumber, Carbon $since): int
    {
        $pattern = "cert_downloads:{$mobileNumber}:*";
        $keys = Cache::getRedis()->keys($pattern);
        
        $count = 0;
        foreach ($keys as $key) {
            $timestamp = (int) substr($key, strrpos($key, ':') + 1);
            if ($timestamp >= $since->timestamp) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Get IP request count since given time
     */
    private static function getIpRequestCount(string $ip, string $type, Carbon $since): int
    {
        $pattern = "ip_requests:{$type}:{$ip}:*";
        $keys = Cache::getRedis()->keys($pattern);
        
        $count = 0;
        foreach ($keys as $key) {
            $timestamp = (int) substr($key, strrpos($key, ':') + 1);
            if ($timestamp >= $since->timestamp) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Get IP limits for different request types
     */
    private static function getIpLimits(string $type): array
    {
        $limits = [
            'general' => [
                'per_minute' => 30,
                'per_hour' => 200,
                'per_day' => 1000,
            ],
            'otp' => [
                'per_minute' => 10,
                'per_hour' => 50,
                'per_day' => 200,
            ],
            'download' => [
                'per_minute' => 5,
                'per_hour' => 20,
                'per_day' => 100,
            ]
        ];
        
        return $limits[$type] ?? $limits['general'];
    }

    /**
     * Get next available time for mobile number
     */
    private static function getNextAvailableTime(string $mobileNumber, string $type): ?Carbon
    {
        $now = now();
        
        if ($type === 'otp') {
            $lastMinute = self::getOtpCount($mobileNumber, $now->copy()->subMinutes(1));
            if ($lastMinute >= 1) {
                return $now->copy()->addMinutes(1);
            }
        } else {
            $lastMinute = self::getDownloadCount($mobileNumber, $now->copy()->subMinutes(1));
            if ($lastMinute >= 1) {
                return $now->copy()->addMinutes(1);
            }
        }
        
        return null;
    }

    /**
     * Get next available time for IP
     */
    private static function getIpNextAvailableTime(string $ip, string $type): ?Carbon
    {
        $now = now();
        $limits = self::getIpLimits($type);
        
        $lastMinute = self::getIpRequestCount($ip, $type, $now->copy()->subMinutes(1));
        if ($lastMinute >= $limits['per_minute']) {
            return $now->copy()->addMinutes(1);
        }
        
        return null;
    }

    /**
     * Get user-friendly error message
     */
    public static function getErrorMessage(array $rateLimitInfo, string $type = 'otp'): string
    {
        $counts = $rateLimitInfo['counts'];
        $limits = $rateLimitInfo['limits'];
        
        if ($type === 'otp') {
            if ($counts['per_one_minute'] >= $limits['per_one_minute']) {
                return 'Please wait 1 minute before requesting another OTP.';
            } elseif ($counts['per_hour'] >= $limits['per_hour']) {
                return 'Hourly OTP limit reached. Please try again in an hour.';
            } elseif ($counts['per_day'] >= $limits['per_day']) {
                return 'Daily OTP limit reached. Please try again tomorrow.';
            }
        } else {
            if ($counts['per_one_minute'] >= $limits['per_one_minute']) {
                return 'Please wait 1 minute before downloading another certificate.';
            } elseif ($counts['per_hour'] >= $limits['per_hour']) {
                return 'Hourly download limit reached. Please try again in an hour.';
            } elseif ($counts['per_day'] >= $limits['per_day']) {
                return 'Daily download limit reached. You can download maximum ' . $limits['per_day'] . ' certificates per day.';
            }
        }
        
        return 'Rate limit exceeded. Please try again later.';
    }
}

