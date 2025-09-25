
<?php

return [
    'HONO_HR_AUTH_URL' => env('HONO_HR_AUTH_URL', 'https://rest.dbclmatrix.com/auth'),
    'HONO_HR_AUTH_TOKEN' => env('HONO_HR_AUTH_TOKEN'),
    

    'BUCKET_PATH' => [
        'BANNER_IMAGE' => 'junior_editor/banner_image/' . date('Ymd'),
        'MAIN_CONTENT_IMAGE' => 'junior_editor/main_content_image/' . date('Ymd'),
        'VIDEO' => 'junior_editor/video_image/' . date('Ymd'),
        'PROCESS_IMAGE' => 'junior_editor/process_image/' . date('Ymd'),
        'SLIDER_IMAGE' => 'junior_editor/slider_image/' . date('Ymd'),
        'PDF_ARCHIVE' => 'PDFArchive/pdf/' . date('dmy'),
        'PDF_THUMBNAIL' => 'PDFArchive/thumb',
        'PDF_THUMBNAIL_LARGE' => 'PDFArchive/thumb-large',
    ],
];