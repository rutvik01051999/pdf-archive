<?php

namespace App\Http\Requests;

use App\Rules\PdfFileUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class ArchiveUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            Log::warning('Unauthorized upload attempt', [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);
            return false;
        }

        // Check if user has admin access
        // You can add more specific authorization logic here
        return true;
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Log::error('ArchiveUploadRequest validation failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all()
        ]);
        
        parent::failedValidation($validator);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'pdf_file' => [
                'required',
                'file',
                'max:51200', // 50MB in KB
                new PdfFileUpload(50), // 50MB max size
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_.,()]+$/' // Allow alphanumeric, spaces, hyphens, underscores, dots, commas, parentheses
            ],
            'category' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_]+$/' // Allow alphanumeric, spaces, hyphens, underscores
            ],
            'published_center' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-_]+$/' // Allow alphanumeric, spaces, hyphens, underscores
            ],
            'edition_name' => [
                'nullable',
                'string',
                'max:200',
                'regex:/^[a-zA-Z0-9\s\-_.,()]+$/'
            ],
            'edition_pageno' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000'
            ],
            'published_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after:1900-01-01'
            ],
            'event' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[a-zA-Z0-9\s\-_.,()!?@#$%&*]+$/'
            ]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'pdf_file.required' => 'Please select a PDF file to upload.',
            'pdf_file.file' => 'The uploaded file must be a valid file.',
            'pdf_file.max' => 'The PDF file must not be larger than 50MB.',
            
            'title.required' => 'Event title is required.',
            'title.max' => 'Event title must not exceed 255 characters.',
            'title.regex' => 'Event title contains invalid characters. Only letters, numbers, spaces, hyphens, underscores, dots, commas, and parentheses are allowed.',
            
            'category.required' => 'Category is required.',
            'category.max' => 'Category must not exceed 100 characters.',
            'category.regex' => 'Category contains invalid characters. Only letters, numbers, spaces, hyphens, and underscores are allowed.',
            
            'published_center.required' => 'Publishing center is required.',
            'published_center.max' => 'Publishing center must not exceed 50 characters.',
            'published_center.regex' => 'Publishing center contains invalid characters. Only letters, numbers, spaces, hyphens, and underscores are allowed.',
            
            'edition_name.max' => 'Edition name must not exceed 200 characters.',
            'edition_name.regex' => 'Edition name contains invalid characters. Only letters, numbers, spaces, hyphens, underscores, dots, commas, and parentheses are allowed.',
            
            'edition_pageno.integer' => 'Page number must be a valid number.',
            'edition_pageno.min' => 'Page number must be at least 1.',
            'edition_pageno.max' => 'Page number must not exceed 10000.',
            
            'published_date.required' => 'Published date is required.',
            'published_date.date' => 'Published date must be a valid date.',
            'published_date.before_or_equal' => 'Published date cannot be in the future.',
            'published_date.after' => 'Published date must be after 1900-01-01.',
            
            'event.max' => 'Event description must not exceed 1000 characters.',
            'event.regex' => 'Event description contains invalid characters.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'pdf_file' => 'PDF file',
            'title' => 'event title',
            'category' => 'category',
            'published_center' => 'publishing center',
            'edition_name' => 'edition name',
            'edition_pageno' => 'page number',
            'published_date' => 'published date',
            'event' => 'event description'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional custom validation logic
            $this->validateFileUploadSecurity($validator);
        });
    }

    /**
     * Additional file upload security validation
     */
    private function validateFileUploadSecurity($validator): void
    {
        $file = $this->file('pdf_file');
        
        if (!$file) {
            return;
        }

        // Check file upload frequency (rate limiting)
        $userId = auth()->user()?->id;
        $ip = request()->ip();
        
        // You can implement rate limiting here using cache
        $cacheKey = "upload_attempts_{$userId}_{$ip}";
        $attempts = cache()->get($cacheKey, 0);
        
        if ($attempts >= 10) { // Max 10 uploads per hour
            Log::warning('Upload rate limit exceeded', [
                'user_id' => $userId,
                'ip' => $ip,
                'attempts' => $attempts
            ]);
            
            $validator->errors()->add('pdf_file', 'Upload rate limit exceeded. Please try again later.');
            return;
        }

        // Increment upload attempts
        cache()->put($cacheKey, $attempts + 1, 3600); // 1 hour

        // Log successful validation
        Log::info('PDF upload validation passed', [
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'user_id' => $userId,
            'ip' => $ip,
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input data
        $this->merge([
            'title' => $this->sanitizeString($this->input('title')),
            'category' => $this->sanitizeString($this->input('category')),
            'published_center' => $this->sanitizeString($this->input('published_center')),
            'edition_name' => $this->sanitizeString($this->input('edition_name')),
            'event' => $this->sanitizeString($this->input('event')),
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
        if (strlen($input) > 1000) {
            $input = substr($input, 0, 1000);
        }

        return $input;
    }
}
