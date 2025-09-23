<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Session Timeout Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the session timeout configuration for admin users.
    | You can adjust these values based on your security requirements.
    |
    */

    'admin' => [
        'inactive_timeout' => 5,        // Inactive timeout in minutes
        'warning_time' => 1,            // Warning time before logout in minutes
        'extend_on_activity' => true,   // Extend session on user activity
        'logout_on_browser_close' => false, // Logout when browser is closed
    ],

    'frontend' => [
        'inactive_timeout' => 30,       // Inactive timeout in minutes for frontend
        'warning_time' => 5,            // Warning time before logout in minutes
        'extend_on_activity' => true,   // Extend session on user activity
        'logout_on_browser_close' => false, // Logout when browser is closed
    ],

    'security_features' => [
        'log_timeouts' => true,         // Log session timeouts
        'log_extensions' => true,       // Log session extensions
        'send_warnings' => true,        // Send warning notifications
        'auto_logout' => true,          // Automatically logout inactive users
    ],

    'warning_messages' => [
        'title' => 'Session Timeout Warning',
        'message' => 'Your session will expire in :minutes minute(s) due to inactivity. Click "Extend Session" to continue working.',
        'extend_button' => 'Extend Session',
        'logout_button' => 'Logout Now',
    ],

    'timeout_messages' => [
        'title' => 'Session Expired',
        'message' => 'Your session has expired due to inactivity. Please log in again.',
        'login_button' => 'Login Again',
    ],
];
