<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pdf;
use App\Models\CategoryPdf;
use App\Models\MatrixReportCenter;
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
        
        // Get archive data for display
        $archiveData = $this->getArchiveData();
        
        return view('admin.dashboard.index', compact('moduleStats', 'archiveData'));
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

    /**
     * Get archive data for dashboard display
     */
    private function getArchiveData()
    {
        try {
            // Get centers from the centers database connection
            $centers = MatrixReportCenter::select('centercode', 'description')
                ->groupBy('centercode')
                ->ordered()
                ->get();
        } catch (\Exception $e) {
            \Log::error('Failed to fetch centers from centers database: ' . $e->getMessage());
            $centers = collect();
        }

        // Get categories from the main database
        $categories = CategoryPdf::active()->ordered()->get();

        // Get some sample archives for display
        $archives = Pdf::with(['categoryRelation', 'center'])
            ->orderBy('upload_date', 'desc')
            ->paginate(18);

        return [
            'centers' => $centers,
            'categories' => $categories,
            'archives' => $archives
        ];
    }

}
