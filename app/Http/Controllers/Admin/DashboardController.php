<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get module statistics
        $moduleStats = $this->getModuleStatistics();
        
        return view('admin.dashboard.index', compact('moduleStats'));
    }
    
    /**
     * Get module statistics for dashboard
     */
    private function getModuleStatistics()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        
        return [
            // Module statistics can be added here as needed
        ];
    }

}
