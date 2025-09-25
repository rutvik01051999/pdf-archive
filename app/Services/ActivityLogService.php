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
    public static function logAdminActivity(string $action, $subject = null, array $properties = [], Request $request = null)
    {
        $user = Auth::user();
        
        if (!$user) {
            return;
        }

        // Get request details if not provided
        if (!$request) {
            $request = request();
        }

        // Prepare base properties
        $baseProperties = array_merge($properties, [
            'type' => 'admin_activity',
            'admin_user_id' => $user->id,
            'admin_user_name' => $user->admin_full_name ?: $user->username,
            'source' => 'admin',
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
            'ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'timestamp' => now()->toDateTimeString(),
            'action_type' => 'page_visit'
        ]);

        // Add subject information if provided
        if ($subject) {
            if ($subject instanceof \Illuminate\Database\Eloquent\Model) {
                // For Eloquent models, we'll use performedOn()
                $log = activity()
                    ->causedBy($user)
                    ->withProperties($baseProperties)
                    ->performedOn($subject);
            } else {
                // For string subjects, add to properties
                $baseProperties['subject_type'] = 'string';
                $baseProperties['subject_value'] = $subject;
                
                $log = activity()
                    ->causedBy($user)
                    ->withProperties($baseProperties);
            }
        } else {
            $log = activity()
                ->causedBy($user)
                ->withProperties($baseProperties);
        }

        $log->log($action);
    }

    /**
     * Log admin login activity
     */
    public static function logAdminLogin(Request $request, $user, array $loginData = [])
    {
        activity()
            ->causedBy($user)
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_data' => $loginData,
                'login_type' => 'admin_login',
                'user_id' => $user->id,
                'username' => $user->username,
                'center' => $user->center ?? null,
                'type' => 'admin_login',
                'source' => 'authentication'
            ])
            ->log("Admin login successful");
    }

    /**
     * Log admin logout activity
     */
    public static function logAdminLogout(Request $request, $user = null)
    {
        $properties = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_type' => 'admin_logout',
            'type' => 'admin_logout',
            'source' => 'authentication'
        ];

        if ($user) {
            $properties['user_id'] = $user->id;
            $properties['username'] = $user->username;
            $properties['center'] = $user->center ?? null;
        }

        activity()
            ->causedBy($user)
            ->withProperties($properties)
            ->log("Admin logout");
    }

    /**
     * Log failed login attempt
     */
    public static function logFailedLogin(Request $request, array $loginAttempt = [])
    {
        activity()
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_attempt' => $loginAttempt,
                'username_attempted' => $request->input('email'),
                'center_attempted' => $request->input('center'),
                'type' => 'failed_login',
                'source' => 'authentication'
            ])
            ->log("Failed login attempt");
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
                'subject_type' => 'Archive',
                'subject_id' => $archiveData['archive_id'] ?? null,
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
                'subject_type' => 'Archive',
                'subject_id' => $archiveId,
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
                'subject_type' => 'Archive',
                'subject_id' => $archiveId,
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
                'subject_type' => 'Archive',
                'subject_id' => $newArchiveId,
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
                'subject_type' => 'Category',
                'subject_id' => $categoryData['category_id'] ?? null,
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
                'subject_type' => 'Center',
                'subject_id' => $centerData['center_id'] ?? null,
                'type' => 'center_management',
                'source' => 'admin'
            ])
            ->log("Center {$action}");
    }

    /**
     * Log special dates management activity
     */
    public static function logSpecialDatesActivity(Request $request, string $action, $specialDateData = [])
    {
        $user = Auth::user();
        
        activity()
            ->causedBy($user)
            ->withProperties([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'special_date_data' => $specialDateData,
                'subject_type' => 'SpecialDate',
                'subject_id' => $specialDateData['special_date_id'] ?? null,
                'type' => 'special_dates_management',
                'source' => 'admin'
            ])
            ->log("Special date {$action}");
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
            'special_dates_activities' => Activity::whereJsonContains('properties->type', 'special_dates_management')->count(),
            'admin_logins' => Activity::whereJsonContains('properties->type', 'admin_login')->count(),
            'admin_logouts' => Activity::whereJsonContains('properties->type', 'admin_logout')->count(),
            'failed_logins' => Activity::whereJsonContains('properties->type', 'failed_login')->count(),
        ];
    }
}

