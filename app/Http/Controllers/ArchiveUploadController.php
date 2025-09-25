<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Traits\GoogleCloudStorageTrait;
use App\Http\Requests\ArchiveUploadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ArchiveUploadController extends Controller
{
    use GoogleCloudStorageTrait;

    /**
     * Show the upload form
     */
    public function showUploadForm(Request $request)
    {
        Log::info('Upload form accessed', [
            'user_id' => auth()->user()?->id,
            'username' => auth()->user()?->username
        ]);
        
        // Log activity for visiting upload page
        try {
            ActivityLogService::logAdminActivity('visited', 'upload_pdf_page', [
                'description' => 'Visited Upload PDF Page',
                'page_type' => 'upload_form',
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'section' => 'file_upload'
            ], $request);
        } catch (\Exception $e) {
            Log::warning('Activity logging failed for upload PDF page visit: ' . $e->getMessage());
        }
        
        try {
            $categories = DB::table('category_pdf')
                ->where('active_status', '1')
                ->orderBy('category')
                ->get();
                
            Log::info('Categories loaded', ['count' => $categories->count()]);
            
            // Get centers from the centers database connection (same as display form)
            try {
                $centers = DB::connection('centers')
                    ->table('matrix_report_centers')
                    ->select('centercode', 'description')
                    ->groupBy('centercode')
                    ->orderBy('description')
                    ->get();
                    
                Log::info('Centers loaded', ['count' => $centers->count()]);
            } catch (\Exception $e) {
                Log::error('Failed to fetch centers from centers database: ' . $e->getMessage());
                $centers = collect();
            }
            
            
            return view('archive.admin.upload', compact('categories', 'centers'));
            
        } catch (\Exception $e) {
            Log::error('Error loading upload form: ' . $e->getMessage());
            return view('archive.admin.upload', [
                'categories' => collect(),
                'centers' => collect()
            ]);
        }
    }

    /**
     * Handle file upload to Google Cloud Storage
     */
    public function upload(ArchiveUploadRequest $request)
    {
        Log::info('Upload method called', [
            'user_id' => auth()->user()?->id,
            'username' => auth()->user()?->username,
            'has_file' => $request->hasFile('pdf_file'),
            'file_size' => $request->hasFile('pdf_file') ? $request->file('pdf_file')->getSize() : 0,
            'all_input' => $request->all()
        ]);
        
        try {
            // Get validated data from request
            $validated = $request->validated();
            Log::info('Form validation passed', ['validated_data' => $validated]);

            $file = $request->file('pdf_file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            
            // Generate unique filename
            $timestamp = Carbon::now()->format('YmdHis');
            $filename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $timestamp . '.' . $extension;
            
            // Create cloud path (same structure as mypdfarchive)
            $centerCode = $validated['published_center'];
            $cloudPath = "PDFArchive/{$centerCode}/{$filename}";
            
            // Use the file's temporary path directly
            $tempPath = $file->getRealPath();
            
            // Debug: Log the file path
            Log::info('Uploading file from path: ' . $tempPath);
            Log::info('File exists: ' . (file_exists($tempPath) ? 'Yes' : 'No'));
            
            // Upload to Google Cloud Storage using trait (with fallback)
            try {
                $signedUrl = $this->uploadFileWithSignedUrl($tempPath, $cloudPath);
                Log::info('Cloud upload successful', ['url' => $signedUrl, 'path' => $cloudPath]);
                
                // Log successful cloud upload
                try {
                    ActivityLogService::logAdminActivity('uploaded', 'cloud_storage', [
                        'description' => 'File Uploaded to Google Cloud Storage',
                        'filename' => $filename,
                        'cloud_path' => $cloudPath,
                        'file_size' => $file->getSize(),
                        'title' => $validated['title'],
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ], $request);
                } catch (\Exception $logError) {
                    Log::warning('Activity logging failed for cloud upload: ' . $logError->getMessage());
                }
                
            } catch (\Exception $e) {
                Log::error('Cloud upload failed: ' . $e->getMessage());
                
                // Log failed cloud upload
                try {
                    ActivityLogService::logAdminActivity('failed', 'cloud_upload', [
                        'description' => 'PDF Upload Failed - Cloud Storage Error',
                        'error' => $e->getMessage(),
                        'filename' => $filename,
                        'cloud_path' => $cloudPath,
                        'file_size' => $file->getSize(),
                        'title' => $validated['title'] ?? null,
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ], $request);
                } catch (\Exception $logError) {
                    Log::warning('Activity logging failed for cloud upload failure: ' . $logError->getMessage());
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed: ' . $e->getMessage()
                ], 500);
            }
            
            // Prepare archive data
            $archiveData = [
                'filename' => $filename,
                'filepath' => $cloudPath,
                'download_url' => $signedUrl,
                'title' => $validated['title'],
                'category' => $validated['category'],
                'edition_name' => $validated['edition_name'] ?? null,
                'edition_code' => $validated['edition_code'] ?? 0,
                'edition_pageno' => $validated['edition_pageno'] ?? 1,
                'published_center' => $validated['published_center'],
                'published_date' => $validated['published_date'],
                'event' => $validated['event'] ?? null,
                'upload_date' => now(),
                'username' => auth()->user()?->username ?? 'admin',
                'auto' => 0, // Manual upload
                'start_time' => now()->format('H:i:s'),
                'end_time' => now()->format('H:i:s'),
            ];
            
            // Save to database
            try {
                $archiveId = DB::table('pdf')->insertGetId($archiveData);
                Log::info('Database insertion successful', ['archive_id' => $archiveId]);
                
                // Update archive data with the ID for logging
                $archiveData['archive_id'] = $archiveId;
                
            } catch (\Exception $e) {
                Log::error('Database insertion failed: ' . $e->getMessage());
                
                // Log failed upload attempt
                try {
                    ActivityLogService::logAdminActivity('failed', 'file_upload', [
                        'description' => 'PDF Upload Failed - Database Error',
                        'error' => $e->getMessage(),
                        'filename' => $filename,
                        'title' => $validated['title'] ?? null,
                        'category' => $validated['category'] ?? null,
                        'center' => $validated['published_center'] ?? null,
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ], $request);
                } catch (\Exception $logError) {
                    Log::warning('Activity logging failed for upload failure: ' . $logError->getMessage());
                }
                
                return redirect()->back()
                    ->with('error', 'Error saving to database: ' . $e->getMessage())
                    ->withInput();
            }
            
            // Log successful upload activity
            try {
                ActivityLogService::logArchiveUpload($request, $archiveData);
                
                // Additional detailed upload activity log
                ActivityLogService::logAdminActivity('created', 'file_upload', [
                    'description' => 'PDF File Uploaded Successfully',
                    'archive_id' => $archiveId,
                    'filename' => $filename,
                    'title' => $validated['title'],
                    'category' => $validated['category'],
                    'center' => $validated['published_center'],
                    'cloud_path' => $cloudPath,
                    'file_size' => $file->getSize(),
                    'edition_name' => $validated['edition_name'] ?? null,
                    'published_date' => $validated['published_date'],
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ], $request);
                
            } catch (\Exception $e) {
                Log::warning('Activity logging failed: ' . $e->getMessage());
            }
            
            return redirect()->route('admin.archive.display')
                ->with('success', 'PDF uploaded successfully to Google Cloud Storage!');
                
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
            
            // Log general upload failure
            try {
                ActivityLogService::logAdminActivity('failed', 'file_upload', [
                    'description' => 'PDF Upload Failed - General Error',
                    'error' => $e->getMessage(),
                    'title' => $request->input('title'),
                    'category' => $request->input('category'),
                    'center' => $request->input('published_center'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ], $request);
            } catch (\Exception $logError) {
                Log::warning('Activity logging failed for general upload failure: ' . $logError->getMessage());
            }
            
            return redirect()->back()
                ->with('error', 'Error uploading file: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Generate thumbnail for uploaded PDF
     */
    public function generateThumbnail(Request $request)
    {
        try {
            $id = $request->id;
            
            $archive = DB::table('pdf')->where('id', $id)->first();
            
            if (!$archive) {
                // Log attempt to generate thumbnail for non-existent archive
                try {
                    ActivityLogService::logAdminActivity('failed', 'thumbnail_generation', [
                        'description' => 'Thumbnail Generation Failed - Archive Not Found',
                        'archive_id' => $id,
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ], $request);
                } catch (\Exception $logError) {
                    Log::warning('Activity logging failed for thumbnail generation failure: ' . $logError->getMessage());
                }
                
                return response()->json(['error' => 'Archive not found'], 404);
            }
            
            // Check if thumbnail already exists
            $thumbnailPath = $this->generateThumbnailPath($archive->filepath, $archive->auto == '1');
            
            if ($thumbnailPath && $this->fileExists($thumbnailPath)) {
                // Log attempt to generate existing thumbnail
                try {
                    ActivityLogService::logAdminActivity('attempted', 'thumbnail_generation', [
                        'description' => 'Thumbnail Already Exists',
                        'archive_id' => $id,
                        'filename' => $archive->filename,
                        'thumbnail_path' => $thumbnailPath,
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ], $request);
                } catch (\Exception $logError) {
                    Log::warning('Activity logging failed for existing thumbnail: ' . $logError->getMessage());
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Thumbnail already exists'
                ]);
            }
            
            // Log thumbnail generation attempt
            try {
                ActivityLogService::logAdminActivity('attempted', 'thumbnail_generation', [
                    'description' => 'Thumbnail Generation Requested',
                    'archive_id' => $id,
                    'filename' => $archive->filename,
                    'title' => $archive->title,
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ], $request);
            } catch (\Exception $logError) {
                Log::warning('Activity logging failed for thumbnail generation: ' . $logError->getMessage());
            }
            
            // TODO: Implement actual thumbnail generation using ImageMagick
            // For now, return success message
            return response()->json([
                'success' => true,
                'message' => 'Thumbnail generation functionality will be implemented'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating thumbnail: ' . $e->getMessage());
            
            // Log thumbnail generation error
            try {
                ActivityLogService::logAdminActivity('failed', 'thumbnail_generation', [
                    'description' => 'Thumbnail Generation Error',
                    'archive_id' => $request->id ?? null,
                    'error' => $e->getMessage(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ], $request);
            } catch (\Exception $logError) {
                Log::warning('Activity logging failed for thumbnail error: ' . $logError->getMessage());
            }
            
            return response()->json(['error' => 'Failed to generate thumbnail'], 500);
        }
    }

    /**
     * Get editions for a specific center
     */
    public function getEditions(Request $request)
    {
        try {
            $centerId = $request->center_id;
            
            // Log editions request
            try {
                ActivityLogService::logAdminActivity('requested', 'editions_data', [
                    'description' => 'Requested Editions for Center',
                    'center_id' => $centerId,
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ], $request);
            } catch (\Exception $logError) {
                Log::warning('Activity logging failed for editions request: ' . $logError->getMessage());
            }
            
            // Connect to matrix database for editions
            $matrixConnection = DB::connection('matrix');
            $editions = $matrixConnection->table('editions')
                ->where('centercode', $centerId)
                ->where('active_status', 1)
                ->orderBy('description')
                ->get();
            
            // Log successful editions retrieval
            try {
                ActivityLogService::logAdminActivity('retrieved', 'editions_data', [
                    'description' => 'Successfully Retrieved Editions',
                    'center_id' => $centerId,
                    'editions_count' => $editions->count(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ], $request);
            } catch (\Exception $logError) {
                Log::warning('Activity logging failed for editions retrieval: ' . $logError->getMessage());
            }
            
            return response()->json([
                'editions' => $editions
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting editions: ' . $e->getMessage());
            
            // Log editions retrieval error
            try {
                ActivityLogService::logAdminActivity('failed', 'editions_data', [
                    'description' => 'Failed to Retrieve Editions',
                    'center_id' => $request->center_id ?? null,
                    'error' => $e->getMessage(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ], $request);
            } catch (\Exception $logError) {
                Log::warning('Activity logging failed for editions error: ' . $logError->getMessage());
            }
            
            return response()->json(['error' => 'Failed to get editions'], 500);
        }
    }
}
