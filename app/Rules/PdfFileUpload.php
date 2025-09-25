<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class PdfFileUpload implements ValidationRule
{
    protected int $maxSize;
    protected array $allowedMimes;
    protected array $allowedExtensions;

    public function __construct(?int $maxSizeInMB = 50)
    {
        $this->maxSize = $maxSizeInMB * 1024 * 1024; // Convert MB to bytes
        $this->allowedMimes = [
            'application/pdf',
            'application/x-pdf',
            'application/acrobat',
            'application/vnd.pdf',
            'text/pdf',
            'text/x-pdf'
        ];
        $this->allowedExtensions = ['pdf'];
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
            // Log file upload attempt for security monitoring
            Log::info('PDF file upload attempt', [
                'filename' => $value->getClientOriginalName(),
                'size' => $value->getSize(),
                'mime_type' => $value->getMimeType(),
                'user_id' => auth()->user()?->id,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Check if file was uploaded successfully
            if (!$value->isValid()) {
                $fail('The :attribute failed to upload properly.');
                return;
            }

            // Check file size
            if ($value->getSize() > $this->maxSize) {
                $maxSizeMB = $this->maxSize / (1024 * 1024);
                $fail("The :attribute must not be greater than {$maxSizeMB} megabytes.");
                return;
            }

            // Check file size minimum (prevent empty files)
            if ($value->getSize() < 1024) { // 1KB minimum
                $fail('The :attribute appears to be empty or too small.');
                return;
            }

            // Check MIME type
            $mimeType = $value->getMimeType();
            if (!in_array($mimeType, $this->allowedMimes)) {
                Log::warning('PDF upload blocked - invalid MIME type', [
                    'filename' => $value->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip()
                ]);
                $fail("The :attribute must be a PDF file. Detected type: {$mimeType}");
                return;
            }

            // Check file extension
            $extension = strtolower($value->getClientOriginalExtension());
            if (!in_array($extension, $this->allowedExtensions)) {
                Log::warning('PDF upload blocked - invalid extension', [
                    'filename' => $value->getClientOriginalName(),
                    'extension' => $extension,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip()
                ]);
                $fail("The :attribute must have a .pdf extension.");
                return;
            }

            // Perform comprehensive security checks
            $this->performSecurityChecks($value, $fail);

            // Validate PDF structure
            $this->validatePdfStructure($value, $fail);

            // Check filename security
            $this->validateFilename($value, $fail);

        } catch (\Exception $e) {
            Log::error('PDF upload validation error', [
                'error' => $e->getMessage(),
                'filename' => $value->getClientOriginalName(),
                'user_id' => auth()->user()?->id,
                'ip' => request()->ip()
            ]);
            $fail("The :attribute failed security validation: " . $e->getMessage());
        }
    }

    /**
     * Perform comprehensive security checks
     */
    private function performSecurityChecks(UploadedFile $file, Closure $fail): void
    {
        $content = file_get_contents($file->getRealPath());
        $filename = $file->getClientOriginalName();

        // Check for PHP code
        $phpPatterns = [
            '/<\?php/i',
            '/<\?=/i',
            '/<\?/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec\s*\(/i',
            '/passthru\s*\(/i'
        ];

        foreach ($phpPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Log::warning('PDF upload blocked - contains PHP code', [
                    'filename' => $filename,
                    'pattern' => $pattern,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip()
                ]);
                $fail('The :attribute contains suspicious code which is not allowed.');
                return;
            }
        }

        // Check for script tags
        if (preg_match('/<script[^>]*>/i', $content)) {
            Log::warning('PDF upload blocked - contains script tags', [
                'filename' => $filename,
                'user_id' => auth()->user()?->id,
                'ip' => request()->ip()
            ]);
            $fail('The :attribute contains script tags which are not allowed.');
            return;
        }

        // Check for executable signatures
        $executableSignatures = [
            "\x4D\x5A", // PE executable
            "\x7F\x45\x4C\x46", // ELF executable
            "\xFE\xED\xFA", // Mach-O executable
            "\xCA\xFE\xBA\xBE", // Mach-O fat binary
            "\xCE\xFA\xED\xFE", // Mach-O 64-bit
            "\xCF\xFA\xED\xFE"  // Mach-O 64-bit
        ];

        foreach ($executableSignatures as $signature) {
            if (strpos($content, $signature) === 0) {
                Log::warning('PDF upload blocked - executable signature detected', [
                    'filename' => $filename,
                    'signature' => bin2hex($signature),
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip()
                ]);
                $fail('The :attribute appears to be an executable which is not allowed.');
                return;
            }
        }

        // Check for embedded objects
        $embeddedObjectPatterns = [
            '/\/EmbeddedFile/i',
            '/\/JavaScript/i',
            '/\/JS\s/i',
            '/\/OpenAction/i',
            '/\/Launch/i'
        ];

        foreach ($embeddedObjectPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Log::warning('PDF upload blocked - embedded objects detected', [
                    'filename' => $filename,
                    'pattern' => $pattern,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip()
                ]);
                $fail('The :attribute contains embedded objects which are not allowed.');
                return;
            }
        }

        // Check for suspicious URLs
        $urlPatterns = [
            '/https?:\/\/[^\s]+/i',
            '/ftp:\/\/[^\s]+/i',
            '/javascript:/i'
        ];

        foreach ($urlPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Log::warning('PDF upload blocked - suspicious URLs detected', [
                    'filename' => $filename,
                    'pattern' => $pattern,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip()
                ]);
                $fail('The :attribute contains suspicious URLs which are not allowed.');
                return;
            }
        }
    }

    /**
     * Validate PDF structure
     */
    private function validatePdfStructure(UploadedFile $file, Closure $fail): void
    {
        $content = file_get_contents($file->getRealPath());

        // Check for PDF header
        if (!preg_match('/^%PDF-/', $content)) {
            Log::warning('PDF upload blocked - invalid PDF header', [
                'filename' => $file->getClientOriginalName(),
                'user_id' => auth()->user()?->id,
                'ip' => request()->ip()
            ]);
            $fail('The :attribute is not a valid PDF file.');
            return;
        }

        // Check for PDF trailer
        if (strpos($content, '%%EOF') === false) {
            Log::warning('PDF upload blocked - missing PDF trailer', [
                'filename' => $file->getClientOriginalName(),
                'user_id' => auth()->user()?->id,
                'ip' => request()->ip()
            ]);
            $fail('The :attribute appears to be corrupted or incomplete.');
            return;
        }

        // Check PDF version (should be 1.0 to 2.0)
        if (preg_match('/%PDF-(\d\.\d)/', $content, $matches)) {
            $version = floatval($matches[1]);
            if ($version < 1.0 || $version > 2.0) {
                Log::warning('PDF upload blocked - unsupported PDF version', [
                    'filename' => $file->getClientOriginalName(),
                    'version' => $version,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip()
                ]);
                $fail('The :attribute uses an unsupported PDF version.');
                return;
            }
        }
    }

    /**
     * Validate filename security
     */
    private function validateFilename(UploadedFile $file, Closure $fail): void
    {
        $filename = $file->getClientOriginalName();

        // Check for null bytes
        if (strpos($filename, "\0") !== false) {
            Log::warning('PDF upload blocked - null bytes in filename', [
                'filename' => $filename,
                'user_id' => auth()->user()?->id,
                'ip' => request()->ip()
            ]);
            $fail('The :attribute filename contains invalid characters.');
            return;
        }

        // Check for path traversal attempts
        $pathTraversalPatterns = ['../', '..\\', '%2e%2e%2f', '%2e%2e%5c'];
        foreach ($pathTraversalPatterns as $pattern) {
            if (stripos($filename, $pattern) !== false) {
                Log::warning('PDF upload blocked - path traversal attempt', [
                    'filename' => $filename,
                    'pattern' => $pattern,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip()
                ]);
                $fail('The :attribute filename contains invalid path characters.');
                return;
            }
        }

        // Check for double extensions
        $parts = explode('.', $filename);
        if (count($parts) > 2) {
            $dangerousExtensions = [
                'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8',
                'pl', 'py', 'jsp', 'asp', 'aspx', 'sh', 'cgi', 'exe',
                'bat', 'cmd', 'com', 'scr', 'vbs', 'js', 'jar'
            ];

            for ($i = 0; $i < count($parts) - 1; $i++) {
                if (in_array(strtolower($parts[$i]), $dangerousExtensions)) {
                    Log::warning('PDF upload blocked - dangerous double extension', [
                        'filename' => $filename,
                        'extension' => $parts[$i],
                        'user_id' => auth()->user()?->id,
                        'ip' => request()->ip()
                    ]);
                    $fail('The :attribute filename contains suspicious extensions.');
                    return;
                }
            }
        }

        // Check filename length
        if (strlen($filename) > 255) {
            Log::warning('PDF upload blocked - filename too long', [
                'filename' => $filename,
                'length' => strlen($filename),
                'user_id' => auth()->user()?->id,
                'ip' => request()->ip()
            ]);
            $fail('The :attribute filename is too long.');
            return;
        }

        // Check for suspicious characters
        $suspiciousChars = ['<', '>', ':', '"', '|', '?', '*', '\\', '/'];
        foreach ($suspiciousChars as $char) {
            if (strpos($filename, $char) !== false) {
                Log::warning('PDF upload blocked - suspicious characters in filename', [
                    'filename' => $filename,
                    'character' => $char,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip()
                ]);
                $fail('The :attribute filename contains invalid characters.');
                return;
            }
        }
    }
}
