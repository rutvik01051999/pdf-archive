<?php

namespace App\Services;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleCloudStorageService
{
    private $storage;
    private $bucketName;
    private $keyFilePath;

    public function __construct()
    {
        $this->bucketName = 'epaper-pdfarchive-live-bucket';
        $this->keyFilePath = storage_path('credentials/google-cloud-key.json');
        
        try {
            $this->storage = new StorageClient([
                'keyFilePath' => $this->keyFilePath
            ]);
        } catch (Exception $e) {
            Log::error('Google Cloud Storage initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload a file to Google Cloud Storage (same as mypdfarchive gu() function)
     */
    public function uploadFile($localFilePath, $cloudPath)
    {
        try {
            // Check if file exists
            if (!file_exists($localFilePath)) {
                throw new Exception("File does not exist: " . $localFilePath);
            }
            
            // Check if file is readable
            if (!is_readable($localFilePath)) {
                throw new Exception("File is not readable: " . $localFilePath);
            }
            
            Log::info('Attempting to upload file: ' . $localFilePath . ' to cloud path: ' . $cloudPath);
            
            $file = fopen($localFilePath, 'r');
            if (!$file) {
                throw new Exception("Could not open file for reading: " . $localFilePath);
            }
            
            $bucket = $this->storage->bucket($this->bucketName);
            
            $object = $bucket->upload($file, [
                'name' => $cloudPath
            ]);
            
            // Generate signed URL with long expiration (same as mypdfarchive)
            $expiredTime = new \DateTime('2099-01-01');
            $object1 = $bucket->object($cloudPath);
            $signedUrl = $object1->signedUrl($expiredTime);
            
            fclose($file);
            Log::info('File uploaded successfully to Google Cloud Storage');
            return $signedUrl;
            
        } catch (Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            Log::error('File path: ' . $localFilePath);
            Log::error('Cloud path: ' . $cloudPath);
            throw $e;
        }
    }

    /**
     * Upload thumbnail to Google Cloud Storage (same as mypdfarchive gu_thumb() function)
     */
    public function uploadThumbnail($localFilePath, $cloudPath)
    {
        try {
            $file = fopen($localFilePath, 'r');
            $bucket = $this->storage->bucket($this->bucketName);
            
            $object = $bucket->upload($file, [
                'name' => $cloudPath
            ]);
            
            // Set public read access for thumbnails (same as mypdfarchive)
            $object->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);
            
            fclose($file);
            return $this->bucketName . '/' . $cloudPath;
            
        } catch (Exception $e) {
            Log::error('Thumbnail upload failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if file exists in bucket (same as mypdfarchive isBucketFileExists() function)
     */
    public function fileExists($cloudPath)
    {
        try {
            $bucket = $this->storage->bucket($this->bucketName);
            $object = $bucket->object($cloudPath);
            return $object->exists();
        } catch (Exception $e) {
            Log::error('File existence check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get file metadata (same as mypdfarchive object_metadata() function)
     */
    public function getFileMetadata($cloudPath)
    {
        try {
            $bucket = $this->storage->bucket($this->bucketName);
            $object = $bucket->object($cloudPath);
            return $object->info();
        } catch (Exception $e) {
            Log::error('File metadata retrieval failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate public URL for file
     */
    public function getPublicUrl($cloudPath)
    {
        return "https://storage.googleapis.com/{$this->bucketName}/{$cloudPath}";
    }

    /**
     * Generate thumbnail path (same logic as mypdfarchive)
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
     * Generate large thumbnail path (same logic as mypdfarchive)
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
     * Generate PDF URL (same logic as mypdfarchive)
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
     * Copy object in bucket (same as mypdfarchive copyObjectInBucket() function)
     */
    public function copyObject($sourcePath, $destinationPath)
    {
        try {
            $bucket = $this->storage->bucket($this->bucketName);
            $object = $bucket->object($sourcePath);
            $copiedObject = $object->copy($this->bucketName, ['name' => $destinationPath]);
            return $copiedObject;
        } catch (Exception $e) {
            Log::error('Object copy failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * List files in bucket with prefix (same as mypdfarchive getBucketFiles() function)
     */
    public function listFiles($prefix = '')
    {
        try {
            $bucket = $this->storage->bucket($this->bucketName);
            $objects = $bucket->objects(['prefix' => $prefix]);
            return $objects;
        } catch (Exception $e) {
            Log::error('File listing failed: ' . $e->getMessage());
            return [];
        }
    }
}