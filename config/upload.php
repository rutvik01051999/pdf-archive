<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for file uploads including size limits and validation
    |
    */

    // File size limits (in KB)
    'max_file_size' => env('MAX_FILE_SIZE', 100 * 1024), // 100MB in KB
    'max_video_size' => env('MAX_VIDEO_SIZE', 100 * 1024), // 100MB in KB
    'max_image_size' => env('MAX_IMAGE_SIZE', 2 * 1024), // 2MB in KB
    'max_document_size' => env('MAX_DOCUMENT_SIZE', 2 * 1024), // 2MB in KB
    
    // Allowed file types with MIME types and extensions
    'allowed_types' => [
        'image' => [
            'mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'max_size' => env('MAX_IMAGE_SIZE', 2 * 1024), // 2MB in KB
        ],
        'video' => [
            'mimes' => ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv', 'video/webm'],
            'extensions' => ['mp4', 'mov', 'avi', 'wmv', 'webm'],
            'max_size' => env('MAX_VIDEO_SIZE', 100 * 1024), // 100MB in KB
        ],
        'document' => [
            'mimes' => ['text/csv', 'text/plain', 'application/csv'],
            'extensions' => ['csv', 'txt'],
            'max_size' => env('MAX_DOCUMENT_SIZE', 2 * 1024), // 2MB in KB
        ],
    ],
    
    // Legacy support (deprecated - use allowed_types instead)
    'allowed_video_types' => ['mp4', 'mov', 'avi', 'wmv', 'webm'],
    'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    
    // Upload settings
    'chunk_size' => 1024 * 1024, // 1MB chunks for large file uploads
    'secure_filenames' => env('SECURE_FILENAMES', true), // Generate secure filenames
    'scan_for_viruses' => env('SCAN_FOR_VIRUSES', false), // Enable virus scanning (requires ClamAV)
    
    // Security settings
    'max_image_dimensions' => [
        'width' => 10000,
        'height' => 10000,
    ],
    'blocked_extensions' => [
        'php', 'phtml', 'php3', 'php4', 'php5', 'pl', 'py', 'jsp', 'asp', 'sh', 'cgi', 'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js'
    ],
    'blocked_mime_types' => [
        'application/x-php', 'application/x-httpd-php', 'text/x-php', 'application/x-executable',
        'application/x-msdownload', 'application/x-msdos-program', 'application/x-winexe'
    ],
];
