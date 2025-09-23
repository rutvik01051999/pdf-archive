<?php

namespace App\Services;

use Imagick;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PdfProcessingService
{
    /**
     * Generate thumbnail from PDF first page
     */
    public function generateThumbnail($pdfPath, $thumbnailPath, $width = 300, $height = 500)
    {
        try {
            // Check if Imagick is available
            if (!extension_loaded('imagick')) {
                throw new \Exception('Imagick extension is not installed');
            }

            // Check if PDF file exists
            if (!file_exists($pdfPath)) {
                throw new \Exception('PDF file not found: ' . $pdfPath);
            }

            // Create directory if it doesn't exist
            $thumbnailDir = dirname($thumbnailPath);
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Generate thumbnail using Imagick
            $imagick = new Imagick($pdfPath . '[0]'); // [0] means first page
            $imagick->setImageColorspace(255);
            $imagick->setImageFormat('jpeg');
            $imagick->thumbnailImage($width, $height, true, true);
            $imagick->writeImage($thumbnailPath);
            $imagick->clear();
            $imagick->destroy();

            return [
                'success' => true,
                'thumbnail_path' => $thumbnailPath,
                'width' => $width,
                'height' => $height
            ];
        } catch (\Exception $e) {
            Log::error('Thumbnail generation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get PDF file information
     */
    public function getPdfInfo($pdfPath)
    {
        try {
            if (!file_exists($pdfPath)) {
                throw new \Exception('PDF file not found: ' . $pdfPath);
            }

            $fileInfo = [
                'size' => filesize($pdfPath),
                'type' => mime_content_type($pdfPath),
                'created' => filectime($pdfPath),
                'modified' => filemtime($pdfPath)
            ];

            // Get PDF page count if Imagick is available
            if (extension_loaded('imagick')) {
                try {
                    $imagick = new Imagick();
                    $imagick->pingImage($pdfPath);
                    $fileInfo['pages'] = $imagick->getNumberImages();
                    $imagick->clear();
                    $imagick->destroy();
                } catch (\Exception $e) {
                    Log::warning('Could not get PDF page count: ' . $e->getMessage());
                    $fileInfo['pages'] = 1; // Default to 1 page
                }
            } else {
                $fileInfo['pages'] = 1; // Default to 1 page if Imagick not available
            }

            return [
                'success' => true,
                'info' => $fileInfo
            ];
        } catch (\Exception $e) {
            Log::error('PDF info retrieval failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate PDF file
     */
    public function validatePdf($file)
    {
        $errors = [];
        
        // Check file extension
        if (strtolower($file->getClientOriginalExtension()) !== 'pdf') {
            $errors[] = 'File must be a PDF';
        }

        // Check MIME type
        if ($file->getMimeType() !== 'application/pdf') {
            $errors[] = 'Invalid file type. Must be a PDF file.';
        }

        // Check file size (max 50MB)
        $maxSize = 50 * 1024 * 1024; // 50MB in bytes
        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size must be less than 50MB';
        }

        // Check if file is corrupted by trying to read it
        try {
            $tempPath = $file->getRealPath();
            if (extension_loaded('imagick')) {
                $imagick = new Imagick();
                $imagick->pingImage($tempPath);
                $imagick->clear();
                $imagick->destroy();
            }
        } catch (\Exception $e) {
            $errors[] = 'PDF file appears to be corrupted or invalid';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Generate unique filename for upload
     */
    public function generateUniqueFilename($originalName, $center, $category)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $timestamp = now()->format('YmdHis');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return "{$center}/{$category}/{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Generate thumbnail filename
     */
    public function generateThumbnailFilename($pdfFilename)
    {
        $pathInfo = pathinfo($pdfFilename);
        return $pathInfo['dirname'] . '/thumbs/' . $pathInfo['filename'] . '_thumb.jpg';
    }

    /**
     * Clean up temporary files
     */
    public function cleanupTempFiles($filePaths)
    {
        foreach ($filePaths as $path) {
            if (file_exists($path)) {
                try {
                    unlink($path);
                } catch (\Exception $e) {
                    Log::warning('Could not delete temp file: ' . $path . ' - ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Get file size in human readable format
     */
    public function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

