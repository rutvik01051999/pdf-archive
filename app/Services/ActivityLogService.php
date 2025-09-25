<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ActivityLogService
{
    /**
     * Log admin activity
     */
    public static function logAdminActivity(string $action, $subject = null, array $properties = [])
    {
        $user = Auth::user();
        
        if (!$user) {
            return;
        }

        $log = activity()
            ->causedBy($user)
            ->withProperties(array_merge($properties, [
                'type' => 'admin_activity',
                'admin_user_id' => $user->id,
                'admin_user_name' => $user->name ?? 'Unknown',
                'source' => 'admin'
            ]));

        if ($subject) {
            $log->performedOn($subject);
        }

        $log->log($action);
    }

    /**
     * Log archive search activity
     */
    public static function logArchiveSearch(Request $request, array $searchParams = [])
    {
        $user = Auth::user();
        
        activity()
            ->causedBy($user)
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'search_params' => $searchParams,
                'type' => 'archive_search',
                'source' => 'admin'
            ])
            ->log("Archive search performed");
    }

    /**
     * Log archive upload activity
     */
    public static function logArchiveUpload(Request $request, $archiveData = [])
    {
        $user = Auth::user();
        
        activity()
            ->causedBy($user)
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'archive_data' => self::sanitizeArchiveData($archiveData),
                'type' => 'archive_upload',
                'source' => 'admin'
            ])
            ->log("Archive uploaded");
    }

    /**
     * Log archive edit activity
     */
    public static function logArchiveEdit(Request $request, $archiveId, $changes = [])
    {
        $user = Auth::user();
        
        activity()
            ->causedBy($user)
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'archive_id' => $archiveId,
                'changes' => $changes,
                'type' => 'archive_edit',
                'source' => 'admin'
            ])
            ->log("Archive edited");
    }

    /**
     * Log archive delete activity
     */
    public static function logArchiveDelete(Request $request, $archiveId, $archiveData = [])
    {
        $user = Auth::user();
        
        activity()
            ->causedBy($user)
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'archive_id' => $archiveId,
                'archive_data' => self::sanitizeArchiveData($archiveData),
                'type' => 'archive_delete',
                'source' => 'admin'
            ])
            ->log("Archive deleted");
    }

    /**
     * Log archive copy activity
     */
    public static function logArchiveCopy(Request $request, $originalArchiveId, $newArchiveId, $copyData = [])
    {
        $user = Auth::user();
        
        activity()
            ->causedBy($user)
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'original_archive_id' => $originalArchiveId,
                'new_archive_id' => $newArchiveId,
                'copy_data' => self::sanitizeArchiveData($copyData),
                'type' => 'archive_copy',
                'source' => 'admin'
            ])
            ->log("Archive copied");
    }

    /**
     * Log PDF download activity
     */
    public static function logPdfDownload(Request $request, $archiveId, $archiveData = [])
    {
        $user = Auth::user();
        
        activity()
            ->causedBy($user)
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'archive_id' => $archiveId,
                'archive_data' => self::sanitizeArchiveData($archiveData),
                'type' => 'pdf_download',
                'source' => 'admin'
            ])
            ->log("PDF downloaded");
    }

    /**
     * Log PDF print activity
     */
    public static function logPdfPrint(Request $request, $archiveId, $archiveData = [])
    {
        $user = Auth::user();
        
        activity()
            ->causedBy($user)
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'archive_id' => $archiveId,
                'archive_data' => self::sanitizeArchiveData($archiveData),
                'type' => 'pdf_print',
                'source' => 'admin'
            ])
            ->log("PDF printed");
    }

    /**
     * Log thumbnail generation activity
     */
    public static function logThumbnailGeneration(Request $request, $archiveId)
    {
        $user = Auth::user();
        
        activity()
            ->causedBy($user)
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'archive_id' => $archiveId,
                'type' => 'thumbnail_generation',
                'source' => 'admin'
            ])
            ->log("Thumbnail generated");
    }

    /**
     * Log category management activity
     */
    public static function logCategoryActivity(Request $request, string $action, $categoryData = [])
    {
        $user = Auth::user();
        
        activity()
            ->causedBy($user)
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'category_data' => $categoryData,
                'type' => 'category_management',
                'source' => 'admin'
            ])
            ->log("Category {$action}");
    }

    /**
     * Log center management activity
     */
    public static function logCenterActivity(Request $request, string $action, $centerData = [])
    {
        $user = Auth::user();
        
        activity()
            ->causedBy($user)
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'center_data' => $centerData,
                'type' => 'center_management',
                'source' => 'admin'
            ])
            ->log("Center {$action}");
    }

    /**
     * Sanitize archive data to remove sensitive information
     */
    private static function sanitizeArchiveData(array $data): array
    {
        $sensitiveFields = ['password', 'token', 'api_key', 'secret', 'filepath'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[HIDDEN]';
            }
        }
        
        return $data;
    }

    /**
     * Get activity statistics
     */
    public static function getActivityStats()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        
        return [
            'total_activities' => Activity::count(),
            'today_activities' => Activity::whereDate('created_at', $today)->count(),
            'this_month_activities' => Activity::where('created_at', '>=', $thisMonth)->count(),
            'archive_searches' => Activity::whereJsonContains('properties->type', 'archive_search')->count(),
            'archive_uploads' => Activity::whereJsonContains('properties->type', 'archive_upload')->count(),
            'archive_edits' => Activity::whereJsonContains('properties->type', 'archive_edit')->count(),
            'archive_deletes' => Activity::whereJsonContains('properties->type', 'archive_delete')->count(),
            'archive_copies' => Activity::whereJsonContains('properties->type', 'archive_copy')->count(),
            'pdf_downloads' => Activity::whereJsonContains('properties->type', 'pdf_download')->count(),
            'pdf_prints' => Activity::whereJsonContains('properties->type', 'pdf_print')->count(),
            'thumbnail_generations' => Activity::whereJsonContains('properties->type', 'thumbnail_generation')->count(),
            'category_activities' => Activity::whereJsonContains('properties->type', 'category_management')->count(),
            'center_activities' => Activity::whereJsonContains('properties->type', 'center_management')->count(),
        ];
    }
}

