<?php

namespace App\Services;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Log;

class GoogleCloudStorageService
{
    protected $storage;
    protected $bucketName;

    public function __construct()
    {
        $this->bucketName = config('services.google_cloud.bucket_name', 'matrix-archive-bucket');
        
        try {
            $this->storage = new StorageClient([
                'keyFilePath' => config('services.google_cloud.key_file_path'),
                'projectId' => config('services.google_cloud.project_id')
            ]);
        } catch (\Exception $e) {
            Log::error('Google Cloud Storage initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload file to Google Cloud Storage
     */
    public function uploadFile($localFilePath, $cloudPath, $makePublic = false)
    {
        try {
            $bucket = $this->storage->bucket($this->bucketName);
            $file = fopen($localFilePath, 'r');
            
            $object = $bucket->upload($file, [
                'name' => $cloudPath,
                'metadata' => [
                    'uploaded_at' => now()->toISOString(),
                    'uploaded_by' => auth()->user()->username ?? 'system'
                ]
            ]);

            // Make file public if requested
            if ($makePublic) {
                $object->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);
            }

            // Generate signed URL for private files
            $expiredTime = new \DateTime('2099-01-01');
            $signedUrl = $object->signedUrl($expiredTime);

            fclose($file);
            
            return [
                'success' => true,
                'url' => $signedUrl,
                'path' => $cloudPath,
                'size' => $object->info()['size'] ?? 0
            ];
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate thumbnail and upload to Google Cloud
     */
    public function uploadThumbnail($localFilePath, $cloudPath)
    {
        try {
            $bucket = $this->storage->bucket($this->bucketName);
            $file = fopen($localFilePath, 'r');
            
            $object = $bucket->upload($file, [
                'name' => $cloudPath
            ]);

            // Make thumbnail public
            $object->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);

            fclose($file);
            
            return [
                'success' => true,
                'path' => $cloudPath,
                'public_url' => "https://storage.googleapis.com/{$this->bucketName}/{$cloudPath}"
            ];
        } catch (\Exception $e) {
            Log::error('Thumbnail upload failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if file exists in bucket
     */
    public function fileExists($cloudPath)
    {
        try {
            $bucket = $this->storage->bucket($this->bucketName);
            $object = $bucket->object($cloudPath);
            return $object->exists();
        } catch (\Exception $e) {
            Log::error('File existence check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete file from bucket
     */
    public function deleteFile($cloudPath)
    {
        try {
            $bucket = $this->storage->bucket($this->bucketName);
            $object = $bucket->object($cloudPath);
            
            if ($object->exists()) {
                $object->delete();
                return ['success' => true];
            }
            
            return ['success' => false, 'error' => 'File not found'];
        } catch (\Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Copy file within bucket
     */
    public function copyFile($sourcePath, $destinationPath)
    {
        try {
            $bucket = $this->storage->bucket($this->bucketName);
            $sourceObject = $bucket->object($sourcePath);
            
            if (!$sourceObject->exists()) {
                return ['success' => false, 'error' => 'Source file not found'];
            }
            
            $copiedObject = $sourceObject->copy($this->bucketName, [
                'name' => $destinationPath
            ]);
            
            return [
                'success' => true,
                'path' => $destinationPath
            ];
        } catch (\Exception $e) {
            Log::error('File copy failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get file metadata
     */
    public function getFileMetadata($cloudPath)
    {
        try {
            $bucket = $this->storage->bucket($this->bucketName);
            $object = $bucket->object($cloudPath);
            
            if (!$object->exists()) {
                return ['success' => false, 'error' => 'File not found'];
            }
            
            $info = $object->info();
            
            return [
                'success' => true,
                'metadata' => [
                    'size' => $info['size'] ?? 0,
                    'content_type' => $info['contentType'] ?? '',
                    'created' => $info['timeCreated'] ?? '',
                    'updated' => $info['updated'] ?? '',
                    'md5_hash' => $info['md5Hash'] ?? ''
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Metadata retrieval failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * List files in bucket with prefix
     */
    public function listFiles($prefix = '')
    {
        try {
            $bucket = $this->storage->bucket($this->bucketName);
            $objects = $bucket->objects([
                'prefix' => $prefix
            ]);
            
            $files = [];
            foreach ($objects as $object) {
                $files[] = [
                    'name' => $object->name(),
                    'size' => $object->info()['size'] ?? 0,
                    'created' => $object->info()['timeCreated'] ?? ''
                ];
            }
            
            return [
                'success' => true,
                'files' => $files
            ];
        } catch (\Exception $e) {
            Log::error('File listing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

