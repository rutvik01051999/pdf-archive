<?php

namespace App\Http\Controllers;

use App\Services\GoogleCloudStorageService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ArchiveUploadController extends Controller
{
    private $storageService;

    public function __construct()
    {
        $this->storageService = new GoogleCloudStorageService();
    }

    /**
     * Show the upload form
     */
    public function showUploadForm()
    {
        try {
            $categories = DB::table('category_pdf')
                ->where('active_status', '1')
                ->orderBy('category')
                ->get();
            
            // Get centers from the centers database connection (same as display form)
            try {
                $centers = DB::connection('centers')
                    ->table('matrix_report_centers')
                    ->select('centercode', 'description')
                    ->groupBy('centercode')
                    ->orderBy('description')
                    ->get();
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
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'pdf_file' => 'required|file|mimes:pdf|max:50000', // 50MB max
                'title' => 'required|string|max:255',
                'category' => 'required|string|max:100',
                'published_center' => 'required|string|max:50',
                'published_date' => 'required|date',
                'edition_name' => 'nullable|string|max:100',
                'edition_code' => 'nullable|integer',
                'edition_pageno' => 'nullable|integer',
                'event' => 'nullable|string',
            ]);

            $file = $request->file('pdf_file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            
            // Generate unique filename
            $timestamp = Carbon::now()->format('YmdHis');
            $filename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $timestamp . '.' . $extension;
            
            // Create cloud path (same structure as mypdfarchive)
            $centerCode = $request->published_center;
            $cloudPath = "PDFArchive/{$centerCode}/{$filename}";
            
            // Use the file's temporary path directly
            $tempPath = $file->getRealPath();
            
            // Debug: Log the file path
            Log::info('Uploading file from path: ' . $tempPath);
            Log::info('File exists: ' . (file_exists($tempPath) ? 'Yes' : 'No'));
            
            // Upload to Google Cloud Storage
            $signedUrl = $this->storageService->uploadFile($tempPath, $cloudPath);
            
            // Prepare archive data
            $archiveData = [
                'filename' => $filename,
                'filepath' => $cloudPath,
                'download_url' => $signedUrl,
                'title' => $request->title,
                'category' => $request->category,
                'edition_name' => $request->edition_name,
                'edition_code' => $request->edition_code ?? 0,
                'edition_pageno' => $request->edition_pageno ?? 1,
                'published_center' => $request->published_center,
                'published_date' => $request->published_date,
                'event' => $request->event,
                'upload_date' => now(),
                'username' => auth()->user()?->username ?? 'admin',
                'auto' => 0, // Manual upload
                'start_time' => now()->format('H:i:s'),
                'end_time' => now()->format('H:i:s'),
            ];
            
            // Save to database
            $archiveId = DB::table('pdf')->insertGetId($archiveData);
            
            // Log upload activity
            ActivityLogService::logArchiveUpload($request, $archiveData);
            
            return redirect()->route('admin.archive.display')
                ->with('success', 'PDF uploaded successfully to Google Cloud Storage!');
                
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
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
                return response()->json(['error' => 'Archive not found'], 404);
            }
            
            // Check if thumbnail already exists
            $thumbnailPath = $this->storageService->generateThumbnailPath($archive->filepath, $archive->auto == '1');
            
            if ($thumbnailPath && $this->storageService->fileExists($thumbnailPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thumbnail already exists'
                ]);
            }
            
            // TODO: Implement actual thumbnail generation using ImageMagick
            // For now, return success message
            return response()->json([
                'success' => true,
                'message' => 'Thumbnail generation functionality will be implemented'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating thumbnail: ' . $e->getMessage());
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
            
            // Connect to matrix database for editions
            $matrixConnection = DB::connection('matrix');
            $editions = $matrixConnection->table('editions')
                ->where('centercode', $centerId)
                ->where('active_status', 1)
                ->orderBy('description')
                ->get();
            
            return response()->json([
                'editions' => $editions
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting editions: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get editions'], 500);
        }
    }
}
