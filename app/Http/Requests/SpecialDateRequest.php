<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class SpecialDateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            Log::warning('Unauthorized special date access attempt', [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'method' => request()->method()
            ]);
            return false;
        }

        // Check if user has admin access
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'special_date' => [
                'required',
                'string',
                'regex:/^\d{2}-\d{2}$/', // Format: DD-MM
                function ($attribute, $value, $fail) {
                    $parts = explode('-', $value);
                    if (count($parts) !== 2) {
                        $fail('Invalid special date format.');
                        return;
                    }
                    
                    $day = intval($parts[0]);
                    $month = intval($parts[1]);
                    
                    // Validate day (1-31)
                    if ($day < 1 || $day > 31) {
                        $fail('Invalid day. Day must be between 1 and 31.');
                        return;
                    }
                    
                    // Validate month (1-12)
                    if ($month < 1 || $month > 12) {
                        $fail('Invalid month. Month must be between 1 and 12.');
                        return;
                    }
                    
                    // Check for invalid date combinations
                    if ($month == 2 && $day > 29) {
                        $fail('Invalid date. February has maximum 29 days.');
                        return;
                    }
                    
                    if (in_array($month, [4, 6, 9, 11]) && $day > 30) {
                        $fail('Invalid date. This month has maximum 30 days.');
                        return;
                    }
                }
            ],
            'description' => [
                'required',
                'string',
                'max:200',
                'min:3',
                'regex:/^[a-zA-Z0-9\s\-_.,()!?@#$%&*]+$/' // Allow alphanumeric, spaces, hyphens, underscores, dots, commas, parentheses, and common punctuation
            ],
            'id' => [
                'nullable',
                'integer',
                'exists:special_dates,id'
            ]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'special_date.required' => 'Special date is required.',
            'special_date.string' => 'Special date must be a valid text.',
            'special_date.regex' => 'Invalid special date format. Please select day and month.',
            
            'description.required' => 'Description is required.',
            'description.string' => 'Description must be a valid text.',
            'description.max' => 'Description must not exceed 200 characters.',
            'description.min' => 'Description must be at least 3 characters.',
            'description.regex' => 'Description contains invalid characters. Only letters, numbers, spaces, and common punctuation are allowed.',
            
            'id.integer' => 'Invalid special date ID.',
            'id.exists' => 'Special date not found.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'special_date' => 'special date',
            'description' => 'description',
            'id' => 'special date ID'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional XSS protection validation
            $this->validateXssProtection($validator);
        });
    }

    /**
     * XSS protection validation
     */
    private function validateXssProtection($validator): void
    {
        $description = $this->input('description');
        
        if (!$description) {
            return;
        }

        // Check for XSS patterns
        $xssPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/<script[^>]*>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i',
            '/<form[^>]*>/i',
            '/<input[^>]*>/i',
            '/<meta[^>]*>/i',
            '/<link[^>]*>/i',
            '/<style[^>]*>.*?<\/style>/is',
            '/<style[^>]*>/i',
            '/expression\s*\(/i',
            '/url\s*\(/i',
            '/vbscript:/i',
            '/data:/i',
            '/<[^>]*>/i' // Any HTML tags
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $description)) {
                Log::warning('XSS attempt detected in special date description', [
                    'description' => $description,
                    'pattern' => $pattern,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
                
                $validator->errors()->add('description', 'Description contains potentially harmful content.');
                return;
            }
        }

        // Check for SQL injection patterns
        $sqlPatterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/delete\s+from/i',
            '/insert\s+into/i',
            '/update\s+set/i',
            '/select\s+.*\s+from/i',
            '/or\s+1\s*=\s*1/i',
            '/and\s+1\s*=\s*1/i',
            '/\'\s*or\s*\'\'/i',
            '/\'\s*and\s*\'\'/i'
        ];

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $description)) {
                Log::warning('SQL injection attempt detected in special date description', [
                    'description' => $description,
                    'pattern' => $pattern,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
                
                $validator->errors()->add('description', 'Description contains potentially harmful content.');
                return;
            }
        }

        // Check for suspicious characters
        $suspiciousChars = [
            '<', '>', '"', "'", '&', '\\', '/', ';', '|', '`', '$'
        ];

        foreach ($suspiciousChars as $char) {
            if (strpos($description, $char) !== false) {
                Log::warning('Suspicious character detected in special date description', [
                    'description' => $description,
                    'character' => $char,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
                
                $validator->errors()->add('description', 'Description contains invalid characters.');
                return;
            }
        }

        // Log successful validation
        Log::info('Special date validation passed', [
            'special_date' => $this->input('special_date'),
            'description' => $description,
            'user_id' => auth()->user()?->id,
            'ip' => request()->ip(),
            'action' => $this->input('id') ? 'update' : 'create'
        ]);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input data
        $this->merge([
            'description' => $this->sanitizeString($this->input('description')),
        ]);
    }

    /**
     * Sanitize string input
     */
    private function sanitizeString(?string $input): ?string
    {
        if (!$input) {
            return null;
        }

        // Remove null bytes and control characters
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Limit length
        if (strlen($input) > 200) {
            $input = substr($input, 0, 200);
        }

        // Remove any remaining HTML tags
        $input = strip_tags($input);

        return $input;
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Additional sanitization after validation
        if (isset($validated['description'])) {
            $validated['description'] = htmlspecialchars($validated['description'], ENT_QUOTES, 'UTF-8');
        }
        
        return $key ? ($validated[$key] ?? $default) : $validated;
    }
}
