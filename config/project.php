
<?php

return [
    'HONO_HR_AUTH_URL' => env('HONO_HR_AUTH_URL', 'https://rest.dbclmatrix.com/auth'),
    'HONO_HR_AUTH_TOKEN' => env('HONO_HR_AUTH_TOKEN'),
    
    // Employee Management API Configuration
    'EMPLOYEE_API_URL' => env('EMPLOYEE_API_URL', 'https://mdm.dbcorp.co.in/getEmployees'),
    'EMPLOYEE_API_TOKEN' => env('EMPLOYEE_API_TOKEN', 'Basic TUFUUklYOnVvaT1rai1YZWxGa3JvcGVbUllCXXVu'),
    'EMPLOYEE_API_TIMEOUT' => env('EMPLOYEE_API_TIMEOUT', 15),
    'EMPLOYEE_API_CONNECT_TIMEOUT' => env('EMPLOYEE_API_CONNECT_TIMEOUT', 10),
    'EMPLOYEE_API_RETRY_ATTEMPTS' => env('EMPLOYEE_API_RETRY_ATTEMPTS', 2),

    'BUCKET_PATH' => [
        'BANNER_IMAGE' => 'junior_editor/banner_image/' . date('Ymd'),
        'MAIN_CONTENT_IMAGE' => 'junior_editor/main_content_image/' . date('Ymd'),
        'VIDEO' => 'junior_editor/video_image/' . date('Ymd'),
        'PROCESS_IMAGE' => 'junior_editor/process_image/' . date('Ymd'),
        'SLIDER_IMAGE' => 'junior_editor/slider_image/' . date('Ymd'),
    ],
];