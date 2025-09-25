<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Upload Security Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains security settings for file uploads
    | including allowed file types, size limits, and security checks.
    |
    */

    'pdf_upload' => [
        'max_size_mb' => 50,
        'max_size_bytes' => 50 * 1024 * 1024, // 50MB
        
        'allowed_mime_types' => [
            'application/pdf',
            'application/x-pdf',
            'application/acrobat',
            'application/vnd.pdf',
            'text/pdf',
            'text/x-pdf'
        ],
        
        'allowed_extensions' => ['pdf'],
        
        'security_checks' => [
            'check_php_code' => true,
            'check_script_tags' => true,
            'check_executable_signatures' => true,
            'check_embedded_objects' => true,
            'check_suspicious_urls' => true,
            'check_filename_security' => true,
            'check_pdf_structure' => true,
            'check_double_extensions' => true,
            'check_null_bytes' => true,
        ],
        
        'dangerous_extensions' => [
            'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8',
            'pl', 'py', 'jsp', 'asp', 'aspx', 'sh', 'cgi', 'exe',
            'bat', 'cmd', 'com', 'scr', 'vbs', 'js', 'jar', 'class',
            'war', 'ear', 'pif'
        ],
        
        'suspicious_patterns' => [
            '/\.php\.pdf$/i',
            '/\.exe\.pdf$/i',
            '/\.bat\.pdf$/i',
            '/\.cmd\.pdf$/i',
            '/\.scr\.pdf$/i',
            '/\.com\.pdf$/i',
            '/\.pif\.pdf$/i',
            '/\.vbs\.pdf$/i',
            '/\.js\.pdf$/i',
            '/\.jar\.pdf$/i',
            '/\.class\.pdf$/i',
            '/\.war\.pdf$/i',
            '/\.ear\.pdf$/i'
        ],
        
        'executable_signatures' => [
            "\x4D\x5A", // PE executable
            "\x7F\x45\x4C\x46", // ELF executable
            "\xFE\xED\xFA", // Mach-O executable
            "\xCA\xFE\xBA\xBE", // Mach-O fat binary
            "\xCE\xFA\xED\xFE", // Mach-O 64-bit
            "\xCF\xFA\xED\xFE"  // Mach-O 64-bit
        ],
        
        'path_traversal_patterns' => [
            '../', '..\\', '%2e%2e%2f', '%2e%2e%5c'
        ],
        
        'suspicious_characters' => [
            '<', '>', ':', '"', '|', '?', '*', '\\', '/'
        ],
        
        'max_filename_length' => 255,
        'min_file_size_bytes' => 1024, // 1KB minimum
    ],
    
    'rate_limiting' => [
        'max_uploads_per_hour' => 10,
        'max_uploads_per_day' => 50,
        'cache_prefix' => 'upload_attempts_',
        'cache_ttl_hours' => 24,
    ],
    
    'logging' => [
        'log_all_attempts' => true,
        'log_blocked_uploads' => true,
        'log_successful_uploads' => true,
        'include_user_info' => true,
        'include_ip_info' => true,
    ],
    
    'storage' => [
        'disk' => 'public',
        'directory' => 'PDFArchive/pdf',
        'organize_by_date' => true,
        'date_format' => 'dmy', // dmy, Ymd, etc.
    ],
    
    'validation' => [
        'client_side_validation' => true,
        'server_side_validation' => true,
        'double_validation' => true, // Both client and server
        'sanitize_filenames' => true,
        'sanitize_form_data' => true,
    ],
    
    'error_handling' => [
        'show_detailed_errors' => false, // Set to false in production
        'log_validation_errors' => true,
        'return_generic_errors' => true, // Return generic errors to users
    ]
];
