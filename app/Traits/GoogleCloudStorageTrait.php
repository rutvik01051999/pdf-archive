<?php

namespace App\Traits;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Log;

trait GoogleCloudStorageTrait
{
    /**
     * Get Google Cloud Storage bucket instance
     */
    public function getStorageBucket()
    {
        $config = [
            'projectId' => env('GOOGLE_CLOUD_PROJECT_ID', 'matrix-160709'),
        ];
        
        // Add key file path if provided (optional - allows JSON-free operation)
        if (env('GOOGLE_CLOUD_KEY_FILE_PATH')) {
            $config['keyFilePath'] = env('GOOGLE_CLOUD_KEY_FILE_PATH');
        }
        
        try {
            Log::info('Initializing Google Cloud Storage client', [
                'project_id' => $config['projectId'],
                'has_key_file' => isset($config['keyFilePath']),
                'bucket_name' => env('GOOGLE_CLOUD_BUCKET_NAME', 'epaper-pdfarchive-live-bucket')
            ]);
            
            $storage = new StorageClient($config);
            $bucket = $storage->bucket(env('GOOGLE_CLOUD_BUCKET_NAME', 'epaper-pdfarchive-live-bucket'));
            
            Log::info('Google Cloud Storage bucket initialized successfully');
            return $bucket;
        } catch (\Exception $e) {
            Log::error('Google Cloud Storage client initialization failed', [
                'error' => $e->getMessage(),
                'project_id' => $config['projectId'],
                'has_key_file' => isset($config['keyFilePath'])
            ]);
            return null;
        }
    }

    /**
     * Upload file directly to Google Cloud Storage bucket
     * Falls back to local storage if GCS is not configured
     */
    public function uploadFileToCloud($tempFilePath, $targetFile, $bucketName = null)
    {
        $bucket = $this->getStorageBucket();
        $bucketName = $bucketName ?: env('GOOGLE_CLOUD_BUCKET_NAME', 'epaper-pdfarchive-live-bucket');
        
        if ($bucket) {
            try {
                // Upload file directly to Google Cloud Storage bucket
                $bucket->upload(
                    fopen($tempFilePath, 'r'),
                    [
                        'name' => $targetFile,
                        'metadata' => [
                            'cacheControl' => 'public, max-age=31536000',
                        ]
                    ]
                );
                
                // Return the direct bucket URL (same as pdf-archive2 format)
                $publicUrl = env('GOOGLE_CLOUD_PUBLIC_URL', 'https://storage.googleapis.com');
                $url = $publicUrl . '/' . $bucketName . '/' . $targetFile;
                
                Log::info('File uploaded directly to Google Cloud Storage', [
                    'target_file' => $targetFile,
                    'url' => $url,
                    'bucket' => $bucketName,
                    'file_size' => filesize($tempFilePath)
                ]);
                
                return $url;
                
            } catch (\Exception $e) {
                Log::error('Direct Google Cloud Storage upload failed, falling back to local storage', [
                    'error' => $e->getMessage(),
                    'target_file' => $targetFile
                ]);
                
                // Fall back to local storage
                return $this->fallbackToLocalStorage($tempFilePath, $targetFile);
            }
        } else {
            Log::info('Google Cloud Storage not configured, using local storage fallback', [
                'target_file' => $targetFile
            ]);
            
            // Fall back to local storage
            return $this->fallbackToLocalStorage($tempFilePath, $targetFile);
        }
    }

    /**
     * Upload file and return signed URL (compatible with existing pdf-archive2 system)
     */
    public function uploadFileWithSignedUrl($tempFilePath, $targetFile, $bucketName = null)
    {
        $bucket = $this->getStorageBucket();
        $bucketName = $bucketName ?: env('GOOGLE_CLOUD_BUCKET_NAME', 'epaper-pdfarchive-live-bucket');
        
        if ($bucket) {
            try {
                // Upload file directly to Google Cloud Storage bucket
                $object = $bucket->upload(
                    fopen($tempFilePath, 'r'),
                    [
                        'name' => $targetFile,
                        'metadata' => [
                            'cacheControl' => 'public, max-age=31536000',
                        ]
                    ]
                );
                
                // Generate signed URL with long expiration (same as pdf-archive2)
                $expiredTime = new \DateTime('2099-01-01');
                $signedUrl = $object->signedUrl($expiredTime);
                
                Log::info('File uploaded to Google Cloud Storage with signed URL', [
                    'target_file' => $targetFile,
                    'signed_url' => $signedUrl,
                    'bucket' => $bucketName,
                    'file_size' => filesize($tempFilePath)
                ]);
                
                return $signedUrl;
                
            } catch (\Exception $e) {
                Log::error('Google Cloud Storage upload with signed URL failed, falling back to local storage', [
                    'error' => $e->getMessage(),
                    'target_file' => $targetFile
                ]);
                
                // Fall back to local storage
                return $this->fallbackToLocalStorage($tempFilePath, $targetFile);
            }
        } else {
            Log::info('Google Cloud Storage not configured, using local storage fallback', [
                'target_file' => $targetFile
            ]);
            
            // Fall back to local storage
            return $this->fallbackToLocalStorage($tempFilePath, $targetFile);
        }
    }

    /**
     * Delete file directly from Google Cloud Storage bucket
     */
    public function deleteFileFromCloud($filePath)
    {
        $bucket = $this->getStorageBucket();
        
        if ($bucket) {
            try {
                $object = $bucket->object($filePath);
                
                if ($object->exists()) {
                    $object->delete();
                    
                    Log::info('File deleted from Google Cloud Storage', [
                        'file_path' => $filePath
                    ]);
                    
                    return true;
                }
                
                Log::warning('File not found in Google Cloud Storage for deletion', [
                    'file_path' => $filePath
                ]);
                
                return false;
                
            } catch (\Exception $e) {
                Log::error('Google Cloud Storage deletion failed', [
                    'error' => $e->getMessage(),
                    'file_path' => $filePath
                ]);
                
                return false;
            }
        } else {
            // Fall back to local storage deletion
            return $this->fallbackDeleteFromLocal($filePath);
        }
    }

    /**
     * Fallback to local storage when GCS is not available
     */
    private function fallbackToLocalStorage($tempFilePath, $targetFile)
    {
        try {
            // Create directory structure in local storage
            $localPath = storage_path('app/public/' . dirname($targetFile));
            if (!file_exists($localPath)) {
                mkdir($localPath, 0755, true);
            }
            
            // Copy file to local storage
            $localFilePath = storage_path('app/public/' . $targetFile);
            copy($tempFilePath, $localFilePath);
            
            // Return the bucket URL structure (even though it's local)
            $publicUrl = env('GOOGLE_CLOUD_PUBLIC_URL', 'https://storage.googleapis.com');
            $bucketName = env('GOOGLE_CLOUD_BUCKET_NAME', 'epaper-pdfarchive-live-bucket');
            $url = $publicUrl . '/' . $bucketName . '/' . $targetFile;
            
            Log::info('File saved to local storage as fallback', [
                'target_file' => $targetFile,
                'url' => $url,
                'local_path' => $localFilePath
            ]);
            
            return $url;
            
        } catch (\Exception $e) {
            Log::error('Local storage fallback failed', [
                'error' => $e->getMessage(),
                'target_file' => $targetFile
            ]);
            
            // Return a placeholder URL
            $publicUrl = env('GOOGLE_CLOUD_PUBLIC_URL', 'https://storage.googleapis.com');
            $bucketName = env('GOOGLE_CLOUD_BUCKET_NAME', 'epaper-pdfarchive-live-bucket');
            return $publicUrl . '/' . $bucketName . "/placeholder.pdf";
        }
    }

    /**
     * Fallback delete from local storage
     */
    private function fallbackDeleteFromLocal($filePath)
    {
        try {
            $localPath = storage_path('app/public/' . $filePath);
            if (file_exists($localPath)) {
                unlink($localPath);
                Log::info('File deleted from local storage fallback', [
                    'file_path' => $filePath
                ]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Local storage deletion fallback failed', [
                'error' => $e->getMessage(),
                'file_path' => $filePath
            ]);
            return false;
        }
    }

    /**
     * Extract file path from URL for deletion
     */
    private function extractFilePathFromUrl($imageUrl)
    {
        if (empty($imageUrl)) {
            return null;
        }

        try {
            // Extract file path from URL
            $parsedUrl = parse_url($imageUrl);
            if (isset($parsedUrl['path'])) {
                $filePath = ltrim($parsedUrl['path'], '/');
                // Remove bucket name from path if present
                $bucketName = env('GOOGLE_CLOUD_BUCKET_NAME', 'epaper-pdfarchive-live-bucket');
                if (strpos($filePath, $bucketName . '/') === 0) {
                    $filePath = substr($filePath, strlen($bucketName) + 1);
                }
                return $filePath;
            }
        } catch (\Exception $e) {
            Log::error('Failed to extract file path from URL: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Delete file from bucket using URL (compatible with existing system)
     */
    public function deleteFromBucket($fileUrl)
    {
        $filePath = $this->extractFilePathFromUrl($fileUrl);
        if ($filePath) {
            return $this->deleteFileFromCloud($filePath);
        }
        return false;
    }

    /**
     * Generate thumbnail path (compatible with existing system)
     */
    public function generateThumbnailPath($filepath, $isAuto = false)
    {
        $path = explode("/", $filepath);
        if (count($path) >= 5) {
            if ($isAuto) {
                // Check if path[5] exists before accessing it
                if (isset($path[5])) {
                    $thumbName = 'PDFArchive/thumb/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[5]));
                } else {
                    $thumbName = 'PDFArchive/thumb/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[4]));
                }
            } else {
                $thumbName = 'PDFArchive/thumb/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[4]));
            }
            return $this->getPublicUrl($thumbName);
        }
        return null;
    }

    /**
     * Generate large thumbnail path (compatible with existing system)
     */
    public function generateLargeThumbnailPath($filepath, $isAuto = false)
    {
        $path = explode("/", $filepath);
        if (count($path) >= 5) {
            if ($isAuto) {
                // Check if path[5] exists before accessing it
                if (isset($path[5])) {
                    $thumbName = 'PDFArchive/thumb-large/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[5]));
                } else {
                    $thumbName = 'PDFArchive/thumb-large/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[4]));
                }
            } else {
                $thumbName = 'PDFArchive/thumb-large/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[4]));
            }
            return $this->getPublicUrl($thumbName);
        }
        return null;
    }

    /**
     * Generate PDF URL (compatible with existing system)
     */
    public function generatePdfUrl($filepath)
    {
        if ($filepath) {
            // Replace 'epaper-archive-storage' with 'epaper-pdfarchive-live-bucket' in filepath
            $pdf_file_path = str_replace('epaper-archive-storage', 'epaper-pdfarchive-live-bucket', $filepath);
            return 'https://storage.googleapis.com/' . $pdf_file_path;
        }
        return null;
    }

    /**
     * Get public URL for file (helper method)
     */
    public function getPublicUrl($cloudPath)
    {
        $publicUrl = env('GOOGLE_CLOUD_PUBLIC_URL', 'https://storage.googleapis.com');
        $bucketName = env('GOOGLE_CLOUD_BUCKET_NAME', 'epaper-pdfarchive-live-bucket');
        return $publicUrl . '/' . $bucketName . '/' . $cloudPath;
    }

    /**
     * Check if file exists in bucket (compatible with existing system)
     */
    public function fileExists($cloudPath)
    {
        try {
            $bucket = $this->getStorageBucket();
            if (!$bucket) {
                return false;
            }
            
            $object = $bucket->object($cloudPath);
            return $object->exists();
        } catch (\Exception $e) {
            Log::error('File existence check failed: ' . $e->getMessage());
            return false;
        }
    }
}