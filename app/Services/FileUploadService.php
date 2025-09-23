<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class FileUploadService
{
    /**
     * Allowed file types with their MIME types and extensions
     */
    private const ALLOWED_TYPES = [
        'image' => [
            'mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'max_size' => 2048, // 2MB in KB
        ],
        'video' => [
            'mimes' => ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv', 'video/webm'],
            'extensions' => ['mp4', 'mov', 'avi', 'wmv', 'webm'],
            'max_size' => 102400, // 100MB in KB
        ],
        'document' => [
            'mimes' => ['text/csv', 'text/plain', 'application/csv'],
            'extensions' => ['csv', 'txt'],
            'max_size' => 2048, // 2MB in KB
        ],
    ];

    /**
     * Upload and validate a file
     *
     * @param UploadedFile $file
     * @param string $type
     * @param string $directory
     * @param string|null $customFilename
     * @return array
     * @throws Exception
     */
    public function uploadFile(UploadedFile $file, string $type, string $directory, ?string $customFilename = null): array
    {
        // Validate file type
        if (!isset(self::ALLOWED_TYPES[$type])) {
            throw new Exception("Invalid file type: {$type}");
        }

        $allowedConfig = self::ALLOWED_TYPES[$type];

        // Validate file size
        if ($file->getSize() > ($allowedConfig['max_size'] * 1024)) {
            throw new Exception("File size exceeds maximum allowed size of {$allowedConfig['max_size']}KB");
        }

        // Validate file extension first (before MIME type to catch double extensions)
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedConfig['extensions'])) {
            throw new Exception("Invalid file extension. Allowed extensions: " . implode(', ', $allowedConfig['extensions']));
        }

        // Check for double extensions
        $this->checkDoubleExtensions($file);

        // Validate MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedConfig['mimes'])) {
            throw new Exception("Invalid file type. Allowed types: " . implode(', ', $allowedConfig['mimes']));
        }

        // Additional security checks
        $this->performSecurityChecks($file, $mimeType, $extension);

        // Generate secure filename
        $filename = $this->generateSecureFilename($file, $customFilename);
        $fullPath = $directory . '/' . $filename;

        // Store the file
        $storedPath = $file->storeAs($directory, $filename, 'public');

        if (!$storedPath) {
            throw new Exception('Failed to store file');
        }

        // Log the upload
        Log::info('File uploaded successfully', [
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
            'user_id' => Auth::check() ? Auth::id() : null,
        ]);

        return [
            'path' => $storedPath,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
        ];
    }

    /**
     * Perform additional security checks on the file
     *
     * @param UploadedFile $file
     * @param string $mimeType
     * @param string $extension
     * @throws Exception
     */
    private function performSecurityChecks(UploadedFile $file, string $mimeType, string $extension): void
    {
        // Check for suspicious file content
        $this->checkFileContent($file, $mimeType);
        
        // Check for null bytes
        $this->checkNullBytes($file);
        
        // Additional image-specific checks
        if (str_starts_with($mimeType, 'image/')) {
            $this->validateImageFile($file);
        }
    }

    /**
     * Check file content for suspicious patterns
     *
     * @param UploadedFile $file
     * @param string $mimeType
     * @throws Exception
     */
    private function checkFileContent(UploadedFile $file, string $mimeType): void
    {
        $content = file_get_contents($file->getRealPath());
        
        // Check for PHP tags
        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
            throw new Exception('File contains PHP code which is not allowed');
        }
        
        // Check for script tags
        if (strpos($content, '<script') !== false) {
            throw new Exception('File contains script tags which are not allowed');
        }
        
        // Check for executable signatures
        $executableSignatures = [
            "\x4D\x5A", // PE executable
            "\x7F\x45\x4C\x46", // ELF executable
            "\xFE\xED\xFA", // Mach-O executable
        ];
        
        foreach ($executableSignatures as $signature) {
            if (strpos($content, $signature) === 0) {
                throw new Exception('File appears to be an executable which is not allowed');
            }
        }
    }

    /**
     * Check for double extensions (e.g., file.jpg.php)
     *
     * @param UploadedFile $file
     * @throws Exception
     */
    private function checkDoubleExtensions(UploadedFile $file): void
    {
        $filename = $file->getClientOriginalName();
        $parts = explode('.', $filename);
        
        if (count($parts) > 2) {
            // Check if any part before the last one is a dangerous extension
            $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'pl', 'py', 'jsp', 'asp', 'sh', 'cgi'];
            
            for ($i = 0; $i < count($parts) - 1; $i++) {
                if (in_array(strtolower($parts[$i]), $dangerousExtensions)) {
                    throw new Exception('File contains suspicious double extension');
                }
            }
        }
    }

    /**
     * Check for null bytes in filename
     *
     * @param UploadedFile $file
     * @throws Exception
     */
    private function checkNullBytes(UploadedFile $file): void
    {
        if (strpos($file->getClientOriginalName(), "\0") !== false) {
            throw new Exception('Filename contains null bytes which is not allowed');
        }
    }

    /**
     * Validate image file using GD
     *
     * @param UploadedFile $file
     * @throws Exception
     */
    private function validateImageFile(UploadedFile $file): void
    {
        $imageInfo = getimagesize($file->getRealPath());
        
        if ($imageInfo === false) {
            throw new Exception('File is not a valid image');
        }
        
        // Check for reasonable dimensions
        if ($imageInfo[0] > 10000 || $imageInfo[1] > 10000) {
            throw new Exception('Image dimensions are too large');
        }
        
        // Additional check: try to create image resource
        $mimeType = $file->getMimeType();
        $image = null;
        
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file->getRealPath());
                break;
            case 'image/png':
                $image = imagecreatefrompng($file->getRealPath());
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file->getRealPath());
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $image = imagecreatefromwebp($file->getRealPath());
                }
                break;
        }
        
        if ($image === false) {
            throw new Exception('File is corrupted or not a valid image');
        }
        
        if ($image) {
            imagedestroy($image);
        }
    }

    /**
     * Generate a secure filename
     *
     * @param UploadedFile $file
     * @param string|null $customFilename
     * @return string
     */
    private function generateSecureFilename(UploadedFile $file, ?string $customFilename = null): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        if ($customFilename) {
            // Sanitize custom filename
            $customFilename = Str::slug($customFilename);
            $customFilename = preg_replace('/[^a-zA-Z0-9\-_]/', '', $customFilename);
            $customFilename = substr($customFilename, 0, 50); // Limit length
            
            if (empty($customFilename)) {
                $customFilename = 'file';
            }
            
            return $customFilename . '_' . time() . '_' . Str::random(10) . '.' . $extension;
        }
        
        // Generate random filename
        return time() . '_' . Str::random(20) . '.' . $extension;
    }

    /**
     * Delete a file
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            $deleted = Storage::disk('public')->delete($path);
            
            if ($deleted) {
                Log::info('File deleted successfully', [
                    'path' => $path,
                    'user_id' => Auth::check() ? Auth::id() : null,
                ]);
            }
            
            return $deleted;
        }
        
        return false;
    }

    /**
     * Get validation rules for a specific file type
     *
     * @param string $type
     * @param bool $required
     * @return array
     */
    public static function getValidationRules(string $type, bool $required = true): array
    {
        if (!isset(self::ALLOWED_TYPES[$type])) {
            throw new Exception("Invalid file type: {$type}");
        }

        $allowedConfig = self::ALLOWED_TYPES[$type];
        $rules = [];
        
        if ($required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        
        $rules[] = 'file';
        $rules[] = 'mimetypes:' . implode(',', $allowedConfig['mimes']);
        $rules[] = 'max:' . $allowedConfig['max_size'];
        
        return $rules;
    }

    /**
     * Get allowed file types
     *
     * @return array
     */
    public static function getAllowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }
}
