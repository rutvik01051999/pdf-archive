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

    public function index(ActivityLogDataTable $dataTable)
    {
        $activityStats = ActivityLogService::getActivityStats();
        
        return $dataTable->render('admin.activitylog.index', compact('activityStats'));
    }

    public function details($id)
    {
        $activity = \Spatie\Activitylog\Models\Activity::with('causer')->findOrFail($id);
        $properties = json_decode($activity->properties ?? '', true) ?? [];
        
        return view('admin.activitylog.details-modal', compact('activity', 'properties'));
    }
}
