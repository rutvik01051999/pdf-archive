<?php

namespace App\Rules;

use App\Services\FileUploadService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SecureFileUpload implements ValidationRule
{
    protected string $type;
    protected int $maxSize;

    public function __construct(string $type, ?int $maxSize = null)
    {
        $this->type = $type;
        $this->maxSize = $maxSize ?? FileUploadService::getAllowedTypes()[$type]['max_size'] ?? 2048;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('The :attribute must be a valid file.');
            return;
        }

        try {
            // Check if file type is allowed
            $allowedTypes = FileUploadService::getAllowedTypes();
            if (!isset($allowedTypes[$this->type])) {
                $fail('Invalid file type specified.');
                return;
            }

            $allowedConfig = $allowedTypes[$this->type];

            // Check file size
            if ($value->getSize() > ($this->maxSize * 1024)) {
                $fail("The :attribute must not be greater than {$this->maxSize} kilobytes.");
                return;
            }

            // Check MIME type
            $mimeType = $value->getMimeType();
            if (!in_array($mimeType, $allowedConfig['mimes'])) {
                $fail("The :attribute must be a file of type: " . implode(', ', $allowedConfig['mimes']) . ".");
                return;
            }

            // Check file extension
            $extension = strtolower($value->getClientOriginalExtension());
            if (!in_array($extension, $allowedConfig['extensions'])) {
                $fail("The :attribute must have one of the following extensions: " . implode(', ', $allowedConfig['extensions']) . ".");
                return;
            }

            // Perform security checks
            $this->performSecurityChecks($value, $fail);

        } catch (\Exception $e) {
            $fail("The :attribute failed security validation: " . $e->getMessage());
        }
    }

    /**
     * Perform additional security checks
     */
    private function performSecurityChecks(UploadedFile $file, Closure $fail): void
    {
        // Check for suspicious file content
        $content = file_get_contents($file->getRealPath());
        
        // Check for PHP tags
        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
            $fail('The :attribute contains PHP code which is not allowed.');
            return;
        }
        
        // Check for script tags
        if (strpos($content, '<script') !== false) {
            $fail('The :attribute contains script tags which are not allowed.');
            return;
        }
        
        // Check for executable signatures
        $executableSignatures = [
            "\x4D\x5A", // PE executable
            "\x7F\x45\x4C\x46", // ELF executable
            "\xFE\xED\xFA", // Mach-O executable
        ];
        
        foreach ($executableSignatures as $signature) {
            if (strpos($content, $signature) === 0) {
                $fail('The :attribute appears to be an executable which is not allowed.');
                return;
            }
        }

        // Check for double extensions
        $filename = $file->getClientOriginalName();
        $parts = explode('.', $filename);
        
        if (count($parts) > 2) {
            $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'pl', 'py', 'jsp', 'asp', 'sh', 'cgi'];
            
            for ($i = 0; $i < count($parts) - 1; $i++) {
                if (in_array(strtolower($parts[$i]), $dangerousExtensions)) {
                    $fail('The :attribute contains suspicious double extension.');
                    return;
                }
            }
        }

        // Check for null bytes
        if (strpos($file->getClientOriginalName(), "\0") !== false) {
            $fail('The :attribute filename contains null bytes which is not allowed.');
            return;
        }

        // Additional image-specific checks
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $this->validateImageFile($file, $fail);
        }
    }

    /**
     * Validate image file using GD
     */
    private function validateImageFile(UploadedFile $file, Closure $fail): void
    {
        $imageInfo = getimagesize($file->getRealPath());
        
        if ($imageInfo === false) {
            $fail('The :attribute is not a valid image.');
            return;
        }
        
        // Check for reasonable dimensions
        if ($imageInfo[0] > 10000 || $imageInfo[1] > 10000) {
            $fail('The :attribute image dimensions are too large.');
            return;
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
            $fail('The :attribute is corrupted or not a valid image.');
            return;
        }
        
        if ($image) {
            imagedestroy($image);
        }
    }
}
