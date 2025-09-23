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
            'projectId' => env('GOOGLE_CLOUD_PROJECT_ID'),
        ];
        
        // Add key file path if provided
        if (env('GOOGLE_CLOUD_KEY_FILE_PATH')) {
            $config['keyFilePath'] = env('GOOGLE_CLOUD_KEY_FILE_PATH');
        }
        
        try {
            $storage = new StorageClient($config);
            return $storage->bucket(env('GOOGLE_CLOUD_BUCKET_NAME', 'dbcorp-events'));
        } catch (\Exception $e) {
            Log::warning('Google Cloud Storage client initialization failed', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get object from bucket
     */
    public function getObject($option)
    {
        $bucket = $this->getStorageBucket();
        if (!$bucket) {
            return null;
        }
        
        try {
            $object = $bucket->objects($option);
            return $object;
        } catch (\Exception $e) {
            Log::error('Failed to get object from bucket', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Upload file directly to Google Cloud Storage bucket
     * Falls back to local storage if GCS is not configured
     */
    public function upload($bucketName, $tempFilePath, $targetFile)
    {
        $bucket = $this->getStorageBucket();
        
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
                
                // Return the direct bucket URL
                $publicUrl = env('GOOGLE_CLOUD_PUBLIC_URL', 'https://cirimage.groupbhaskar.in');
                $url = $publicUrl . '/' . $targetFile;
                
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
     * Delete file directly from Google Cloud Storage bucket
     */
    public function delete($filePath)
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
            $publicUrl = env('GOOGLE_CLOUD_PUBLIC_URL', 'https://cirimage.groupbhaskar.in');
            $url = $publicUrl . '/' . $targetFile;
            
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
            $publicUrl = env('GOOGLE_CLOUD_PUBLIC_URL', 'https://cirimage.groupbhaskar.in');
            return $publicUrl . "/placeholder.jpg";
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
}