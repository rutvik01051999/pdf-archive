<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Certificate Download Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the rate limiting configuration for certificate
    | download functionality. You can adjust these values based on your
    | requirements and server capacity.
    |
    */

    'otp_requests' => [
        'per_minute' => 5,        // Max OTP requests per minute per mobile/IP
        'per_hour' => 10,         // Max OTP requests per hour per mobile/IP
        'per_day' => 100,         // Max OTP requests per day per mobile
    ],

    'downloads' => [
        'per_minute' => 3,        // Max downloads per minute per mobile/IP
        'per_hour' => 5,          // Max downloads per hour per mobile/IP
        'per_day' => 3,           // Max downloads per day per mobile
    ],

    'ip_limits' => [
        'per_minute' => 10,       // Max requests per minute per IP
        'per_hour' => 50,         // Max requests per hour per IP
        'per_day' => 100,         // Max requests per day per IP
    ],

    'cache_prefix' => [
        'otp_requests' => 'cert_otp_requests',
        'downloads' => 'cert_downloads',
        'ip_requests' => 'cert_ip_requests',
    ],

    'error_messages' => [
        'too_many_otp_requests' => 'Too many OTP requests. Please try again later.',
        'too_many_downloads' => 'Daily download limit exceeded. You can download maximum :limit certificates per day.',
        'too_many_ip_requests' => 'Too many requests from this IP address. Please try again later.',
        'retry_after' => 'Please try again in :seconds seconds.',
    ],
];
