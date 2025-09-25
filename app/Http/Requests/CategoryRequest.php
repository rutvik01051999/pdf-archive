<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            Log::warning('Unauthorized category access attempt', [
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
            'category_name' => [
                'required',
                'string',
                'max:100',
                'min:2',
                'regex:/^[a-zA-Z0-9\s\-_.,()]+$/', // Allow alphanumeric, spaces, hyphens, underscores, dots, commas, parentheses
                'unique:category_pdf,category,' . $this->input('id') // Unique except for current record when editing
            ],
            'id' => [
                'nullable',
                'integer',
                'exists:category_pdf,id'
            ]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_name.required' => 'Category name is required.',
            'category_name.string' => 'Category name must be a valid text.',
            'category_name.max' => 'Category name must not exceed 100 characters.',
            'category_name.min' => 'Category name must be at least 2 characters.',
            'category_name.regex' => 'Category name contains invalid characters. Only letters, numbers, spaces, hyphens, underscores, dots, commas, and parentheses are allowed.',
            'category_name.unique' => 'This category name already exists.',
            'id.integer' => 'Invalid category ID.',
            'id.exists' => 'Category not found.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_name' => 'category name',
            'id' => 'category ID'
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
        $categoryName = $this->input('category_name');
        
        if (!$categoryName) {
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
            if (preg_match($pattern, $categoryName)) {
                Log::warning('XSS attempt detected in category name', [
                    'category_name' => $categoryName,
                    'pattern' => $pattern,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
                
                $validator->errors()->add('category_name', 'Category name contains potentially harmful content.');
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
            if (preg_match($pattern, $categoryName)) {
                Log::warning('SQL injection attempt detected in category name', [
                    'category_name' => $categoryName,
                    'pattern' => $pattern,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
                
                $validator->errors()->add('category_name', 'Category name contains potentially harmful content.');
                return;
            }
        }

        // Check for suspicious characters
        $suspiciousChars = [
            '<', '>', '"', "'", '&', '\\', '/', ';', '|', '`', '$'
        ];

        foreach ($suspiciousChars as $char) {
            if (strpos($categoryName, $char) !== false) {
                Log::warning('Suspicious character detected in category name', [
                    'category_name' => $categoryName,
                    'character' => $char,
                    'user_id' => auth()->user()?->id,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
                
                $validator->errors()->add('category_name', 'Category name contains invalid characters.');
                return;
            }
        }

        // Log successful validation
        Log::info('Category validation passed', [
            'category_name' => $categoryName,
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
            'category_name' => $this->sanitizeString($this->input('category_name')),
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
        if (strlen($input) > 100) {
            $input = substr($input, 0, 100);
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
        if (isset($validated['category_name'])) {
            $validated['category_name'] = htmlspecialchars($validated['category_name'], ENT_QUOTES, 'UTF-8');
        }
        
        return $key ? ($validated[$key] ?? $default) : $validated;
    }
}
