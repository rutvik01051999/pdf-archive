<?php

namespace App\Http\Controllers;

use App\Models\PdfArchive;
use App\Models\ArchiveCategory;
use App\Models\ArchiveCenter;
use App\Services\GoogleCloudStorageService;
use App\Services\PdfProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PdfArchiveController extends Controller
{
    protected $googleCloudService;
    protected $pdfProcessingService;

    public function __construct(GoogleCloudStorageService $googleCloudService, PdfProcessingService $pdfProcessingService)
    {
        $this->googleCloudService = $googleCloudService;
        $this->pdfProcessingService = $pdfProcessingService;
    }

    /**
     * Display the main archive interface
     */
    public function index()
    {
        $centers = ArchiveCenter::active()->orderBy('description')->get();
        $categories = ArchiveCategory::active()->orderBy('name')->get();
        
        return view('archive.index', compact('centers', 'categories'));
    }

    /**
     * Display archive listings with filters
     */
    public function display(Request $request)
    {
        $query = PdfArchive::active()->with(['center', 'category']);

        // Apply filters
        if ($request->filled('center')) {
            $query->byCenter($request->center);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('edition_name')) {
            $query->where('edition_name', 'like', '%' . $request->edition_name . '%');
        }

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('upload_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('upload_date', '<=', $request->end_date);
        }

        $archives = $query->orderBy('upload_date', 'desc')->paginate(20);
        

        $centers = ArchiveCenter::active()->orderBy('description')->get();
        $categories = ArchiveCategory::active()->orderBy('name')->get();

        return view('archive.display', compact('archives', 'centers', 'categories'));
    }

    /**
     * Upload PDF file
     */
    public function upload(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:51200', // 50MB max
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'center' => 'required|string|max:255',
            'edition_name' => 'nullable|string|max:255',
            'edition_code' => 'nullable|string|max:255',
            'page_number' => 'nullable|integer|min:1',
            'is_matrix_edition' => 'boolean',
            'remarks' => 'nullable|string'
        ]);

        try {
            $file = $request->file('pdf_file');
            
            // Validate PDF file
            $validation = $this->pdfProcessingService->validatePdf($file);
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'File validation failed: ' . implode(', ', $validation['errors'])
                ], 400);
            }

            // Generate unique filename
            $cloudFilename = $this->pdfProcessingService->generateUniqueFilename(
                $file->getClientOriginalName(),
                $request->center,
                $request->category
            );

            // Upload to local storage first
            $localPath = $file->store('temp', 'local');
            $fullLocalPath = storage_path('app/' . $localPath);

            // Upload to Google Cloud Storage
            $uploadResult = $this->googleCloudService->uploadFile($fullLocalPath, $cloudFilename);
            
            if (!$uploadResult['success']) {
                Storage::disk('local')->delete($localPath);
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed: ' . $uploadResult['error']
                ], 500);
            }

            // Generate thumbnail
            $thumbnailFilename = $this->pdfProcessingService->generateThumbnailFilename($cloudFilename);
            $thumbnailPath = storage_path('app/temp/thumb_' . basename($localPath));
            
            $thumbnailResult = $this->pdfProcessingService->generateThumbnail($fullLocalPath, $thumbnailPath);
            
            $thumbnailCloudPath = null;
            if ($thumbnailResult['success']) {
                // Upload thumbnail to Google Cloud
                $thumbnailUploadResult = $this->googleCloudService->uploadThumbnail($thumbnailPath, $thumbnailFilename);
                if ($thumbnailUploadResult['success']) {
                    $thumbnailCloudPath = $thumbnailUploadResult['path'];
                }
            }

            // Get file info
            $fileInfo = $this->pdfProcessingService->getPdfInfo($fullLocalPath);

            // Create database record
            $archive = PdfArchive::create([
                'title' => $request->title,
                'category' => $request->category,
                'center' => $request->center,
                'edition_name' => $request->edition_name,
                'edition_code' => $request->edition_code,
                'page_number' => $request->page_number,
                'pdf_file_path' => $cloudFilename,
                'thumbnail_path' => $thumbnailCloudPath,
                'google_cloud_path' => $uploadResult['path'],
                'google_cloud_thumbnail_path' => $thumbnailCloudPath,
                'file_size' => $fileInfo['success'] ? $fileInfo['info']['size'] : 0,
                'file_type' => $file->getMimeType(),
                'is_matrix_edition' => $request->boolean('is_matrix_edition', false),
                'auto_generated' => false,
                'uploaded_by' => Auth::user()->username ?? 'system',
                'upload_date' => now(),
                'remarks' => $request->remarks
            ]);

            // Clean up temporary files
            $this->pdfProcessingService->cleanupTempFiles([$fullLocalPath, $thumbnailPath]);
            Storage::disk('local')->delete($localPath);

            return response()->json([
                'success' => true,
                'message' => 'PDF uploaded successfully',
                'archive' => $archive
            ]);

        } catch (\Exception $e) {
            Log::error('PDF upload failed: ' . $e->getMessage());
            
            // Clean up any temporary files
            if (isset($localPath)) {
                Storage::disk('local')->delete($localPath);
            }
            if (isset($thumbnailPath)) {
                $this->pdfProcessingService->cleanupTempFiles([$thumbnailPath]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View PDF file
     */
    public function view($id)
    {
        $archive = PdfArchive::findOrFail($id);
        
        return view('archive.view', compact('archive'));
    }

    /**
     * Download PDF file
     */
    public function download($id)
    {
        $archive = PdfArchive::findOrFail($id);
        
        try {
            // Get signed URL from Google Cloud Storage
            $metadata = $this->googleCloudService->getFileMetadata($archive->google_cloud_path);
            
            if (!$metadata['success']) {
                return redirect()->back()->with('error', 'File not found or access denied');
            }

            return redirect($archive->google_cloud_path);
            
        } catch (\Exception $e) {
            Log::error('PDF download failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Download failed');
        }
    }

    /**
     * Delete PDF archive
     */
    public function delete($id)
    {
        $archive = PdfArchive::findOrFail($id);
        
        try {
            // Delete from Google Cloud Storage
            if ($archive->google_cloud_path) {
                $this->googleCloudService->deleteFile($archive->google_cloud_path);
            }
            
            if ($archive->google_cloud_thumbnail_path) {
                $this->googleCloudService->deleteFile($archive->google_cloud_thumbnail_path);
            }

            // Delete database record
            $archive->delete();

            return response()->json([
                'success' => true,
                'message' => 'Archive deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Archive deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Deletion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get archive statistics
     */
    public function statistics()
    {
        $stats = [
            'total_archives' => PdfArchive::active()->count(),
            'today_uploads' => PdfArchive::active()->whereDate('upload_date', today())->count(),
            'this_month_uploads' => PdfArchive::active()->whereMonth('upload_date', now()->month)->count(),
            'centers_count' => PdfArchive::active()->distinct('center')->count('center'),
            'categories_count' => PdfArchive::active()->distinct('category')->count('category'),
            'matrix_editions' => PdfArchive::active()->where('is_matrix_edition', true)->count()
        ];

        return response()->json($stats);
    }

    /**
     * Get categories for AJAX
     */
    public function getCategories(Request $request)
    {
        $categories = ArchiveCategory::active()
            ->when($request->filled('center'), function($query) use ($request) {
                return $query->where('center_code', $request->center);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        return response()->json($categories);
    }

    /**
     * Search archives
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $archives = PdfArchive::active()
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('edition_name', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%");
            })
            ->with(['center', 'category'])
            ->limit(10)
            ->get();

        return response()->json($archives);
    }
}