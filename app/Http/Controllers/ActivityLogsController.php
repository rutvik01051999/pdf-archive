<?php

namespace App\Http\Controllers;

use App\DataTables\ActivityLogDataTable;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ActivityLogsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'rotate.session', 'inactive.admin.logout']);
    }

    public function index(ActivityLogDataTable $dataTable, Request $request)
    {
        // Log page visit
        ActivityLogService::logAdminActivity('visited activity logs page', null, [], $request);
        
        $activityStats = ActivityLogService::getActivityStats();
        
        return $dataTable->render('admin.activitylog.index', compact('activityStats'));
    }

    public function details($id, Request $request)
    {
        // Log activity details view
        ActivityLogService::logAdminActivity('viewed activity log details', null, [
            'activity_id' => $id,
            'action' => 'view_details',
            'viewed_activity_type' => 'activity_log_details'
        ], $request);
        
        $activity = \Spatie\Activitylog\Models\Activity::with('causer')->findOrFail($id);
        $properties = json_decode($activity->properties ?? '', true) ?? [];
        
        return view('admin.activitylog.details-modal', compact('activity', 'properties'));
    }
}
