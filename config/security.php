<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Content Protection Management (CPM) Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains comprehensive security settings for the application
    | including content protection, access control, and security headers.
    |
    */

    'headers' => [
        'x_frame_options' => 'SAMEORIGIN',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=(), unload=(self)',
        'strict_transport_security' => [
            'enabled' => env('HTTPS_ENABLED', false),
            'max_age' => 31536000, // 1 year
            'include_subdomains' => true,
            'preload' => true,
        ],
        'content_security_policy' => [
            'enabled' => true,
            'directives' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://code.jquery.com https://cdn.ckeditor.com https://cdn.datatables.net https://checkout.razorpay.com https://junioreditor.groupbhaskar.in",
                'script-src-elem' => "'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://code.jquery.com https://cdn.ckeditor.com https://cdn.datatables.net https://checkout.razorpay.com https://junioreditor.groupbhaskar.in",
                'style-src' => "'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.bunny.net https://checkout.razorpay.com https://code.jquery.com https://cdn.datatables.net",
                'style-src-elem' => "'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.bunny.net https://checkout.razorpay.com https://code.jquery.com https://cdn.datatables.net",
                'font-src' => "'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://fonts.bunny.net",
                'img-src' => "'self' data: https: blob: http:",
                'connect-src' => "'self' https: http:",
                'media-src' => "'self' https: blob: http:",
                'object-src' => "'none'",
                'child-src' => "'self' blob: https://api.razorpay.com https://www.google.com https://maps.google.com https://www.gstatic.com",
                'frame-src' => "'self' blob: https://api.razorpay.com https://www.google.com https://maps.google.com https://www.gstatic.com",
                'frame-ancestors' => "'self'",
                'form-action' => "'self'",
                'base-uri' => "'self'",
                'manifest-src' => "'self'",
            ],
        ],
    ],

    'content_protection' => [
        'hotlink_protection' => true,
        'right_click_disable' => false, // Can be enabled for sensitive content
        'text_selection_disable' => false, // Can be enabled for sensitive content
        'print_disable' => false, // Can be enabled for sensitive content
        'download_protection' => true,
        'watermark' => [
            'enabled' => false,
            'text' => 'Confidential',
            'opacity' => 0.3,
        ],
    ],

    'access_control' => [
        'ip_whitelist' => [
            'enabled' => false,
            'ips' => [
                // Add trusted IP addresses here
            ],
        ],
        'ip_blacklist' => [
            'enabled' => true,
            'ips' => [
                // Add blocked IP addresses here
            ],
        ],
        'geo_blocking' => [
            'enabled' => false,
            'blocked_countries' => [
                // Add country codes to block
            ],
        ],
        'user_agent_blocking' => [
            'enabled' => true,
            'blocked_patterns' => [
                'bot', 'crawler', 'spider', 'scraper', 'wget', 'curl',
                'python-requests', 'java', 'go-http', 'okhttp',
            ],
        ],
    ],

    'rate_limiting' => [
        'global' => [
            'enabled' => false,
            'max_requests' => 1000,
            'decay_minutes' => 60,
        ],
        'api' => [
            'enabled' => false,
            'max_requests' => 100,
            'decay_minutes' => 1,
        ],
        'download' => [
            'enabled' => false,
            'max_downloads' => 10,
            'decay_minutes' => 60,
        ],
    ],

    'encryption' => [
        'sensitive_data' => [
            'enabled' => true,
            'fields' => [
                'email', 'phone', 'address', 'personal_info',
            ],
        ],
        'file_encryption' => [
            'enabled' => false,
            'algorithm' => 'AES-256-CBC',
        ],
    ],

    'monitoring' => [
        'security_events' => [
            'enabled' => true,
            'log_level' => 'info',
            'events' => [
                'failed_login', 'suspicious_activity', 'file_upload',
                'admin_access', 'data_export', 'configuration_change',
            ],
        ],
        'intrusion_detection' => [
            'enabled' => true,
            'threshold' => 5, // Failed attempts before flagging
            'time_window' => 300, // 5 minutes
        ],
    ],

    'backup' => [
        'enabled' => true,
        'frequency' => 'daily',
        'retention_days' => 30,
        'encrypt' => true,
    ],

    'compliance' => [
        'gdpr' => [
            'enabled' => true,
            'data_retention_days' => 2555, // 7 years
            'right_to_erasure' => true,
        ],
        'audit_logging' => [
            'enabled' => true,
            'retention_days' => 365,
        ],
    ],
];
