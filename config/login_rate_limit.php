<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Login Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the rate limiting configuration for admin login
    | attempts. You can adjust these values based on your security requirements.
    |
    */

    'attempts' => [
        'per_minute' => 999999,   // Max login attempts per minute per IP (disabled)
        'per_hour' => 999999,     // Max login attempts per hour per IP (disabled)
        'per_day' => 999999,     // Max login attempts per day per IP (disabled)
    ],

    'account_lockout' => [
        'max_attempts' => 999999, // Max failed attempts before account lockout (disabled)
        'lockout_duration' => 0,  // Lockout duration in minutes (disabled)
        'per_user' => false,      // Disable per-user lockout
        'per_ip' => false,        // Disable per-IP lockout
    ],

    'cache_prefix' => [
        'login_attempts' => 'login_attempts',
        'account_lockout' => 'account_lockout',
        'ip_lockout' => 'ip_lockout',
    ],

    'error_messages' => [
        'too_many_attempts' => 'Too many login attempts. Please try again in :time.',
        'account_locked' => 'Your account has been temporarily locked due to too many failed login attempts. Please try again in :time.',
        'ip_locked' => 'This IP address has been temporarily blocked due to too many failed login attempts. Please try again in :time.',
        'contact_admin' => 'If you believe this is an error, please contact your administrator.',
    ],

    'notifications' => [
        'enable_email_alerts' => true,     // Send email alerts for lockouts
        'admin_email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'alert_threshold' => 3,            // Send alert after this many failed attempts
    ],

    'security_features' => [
        'log_all_attempts' => true,        // Log all login attempts
        'log_successful_logins' => true,   // Log successful logins
        'log_failed_attempts' => true,     // Log failed attempts
        'log_lockouts' => true,            // Log account/IP lockouts
        'require_captcha_after' => 3,      // Require CAPTCHA after this many failed attempts
    ],
];
