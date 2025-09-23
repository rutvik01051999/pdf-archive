<?php

namespace App\Http\Controllers;

use App\Models\PdfArchive;
use App\Models\ArchiveCategory;
use App\Models\ArchiveCenter;
use App\Models\ArchiveLogin;
use App\Models\ArchiveLoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ArchiveAdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_archives' => PdfArchive::count(),
            'active_archives' => PdfArchive::active()->count(),
            'total_users' => ArchiveLogin::count(),
            'active_users' => ArchiveLogin::active()->count(),
            'total_centers' => ArchiveCenter::active()->count(),
            'total_categories' => ArchiveCategory::active()->count(),
            'today_uploads' => PdfArchive::whereDate('upload_date', today())->count(),
            'this_month_uploads' => PdfArchive::whereMonth('upload_date', now()->month)->count(),
        ];

        $recentUploads = PdfArchive::with(['center', 'category'])
            ->orderBy('upload_date', 'desc')
            ->limit(10)
            ->get();

        $recentLogins = ArchiveLoginLog::with('center')
            ->orderBy('login_time', 'desc')
            ->limit(10)
            ->get();

        return view('archive.admin.dashboard', compact('stats', 'recentUploads', 'recentLogins'));
    }

    /**
     * Manage archive categories
     */
    public function categories()
    {
        // Get centers from the centers database connection
        try {
            $centers = DB::connection('centers')
                ->table('matrix_report_centers')
                ->select('centercode', 'description')
                ->groupBy('centercode')
                ->orderBy('description')
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to fetch centers from reports database: ' . $e->getMessage());
            $centers = collect();
        }
        
        return view('archive.admin.categories', compact('centers'));
    }

    /**
     * Get categories data for DataTables (matching mypdfarchive structure)
     */
    public function getCategoriesData()
    {
        try {
            $categories = DB::table('category_pdf')
                ->select(['id', 'category', 'active_status'])
                ->where('active_status', '1')
                ->orderBy('category')
                ->get();

            Log::info('Categories found: ' . $categories->count());

            $data = array();
            $a = 1;
            foreach ($categories as $key => $category) {
                $data[$key][] = $a;
                $data[$key][] = $category->category;
                $data[$key][] = '<a href="javascript:void(0);" class="btn btn-warning btn-xs btn-edit" style="margin-right:10px" title="Edit" data-id="'.$category->id.'"><i class="fa fa-edit"></i></a><a href="javascript:void(0);" class="btn btn-danger btn-xs btn-delete" style="margin-right:10px" title="Delete" data-id="'.$category->id.'"><i class="fa fa-times"></i></a>';
                $a++;
            }

            $data_table['draw'] = 1;
            $data_table['recordsTotal'] = count($categories);
            $data_table['recordsFiltered'] = count($categories);
            $data_table['data'] = $data;

            Log::info('DataTable response: ' . json_encode($data_table));

            return response()->json($data_table);
        } catch (\Exception $e) {
            Log::error('Error in getCategoriesData: ' . $e->getMessage());
            return response()->json([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store new category (matching mypdfarchive structure)
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:category_pdf,category,' . ($request->id ?: 'NULL'),
        ]);

        if ($request->id == '') {
            // Add new category
            $insertId = DB::table('category_pdf')->insertGetId([
                'category' => $request->category_name,
                'active_status' => '1',
            ]);
        } else {
            // Update existing category
            DB::table('category_pdf')
                ->where('id', $request->id)
                ->update([
                    'category' => $request->category_name,
                ]);
            $insertId = $request->id;
        }

        if ($insertId) {
            $response['status'] = "success";
            $response['message'] = "Category has been added successfully.";
        } else {
            $response['status'] = "fail";
            $response['message'] = "Category has been not added successfully.";
        }

        return response()->json($response);
    }

    /**
     * Edit category (matching mypdfarchive structure)
     */
    public function editCategory(Request $request, $id)
    {
        $category = DB::table('category_pdf')
            ->where('id', $id)
            ->first();

        $records = array(
            "id" => $category->id,
            "category_name" => $category->category
        );

        return response()->json($records);
    }

    /**
     * Delete category (matching mypdfarchive structure - soft delete)
     */
    public function deleteCategory($id)
    {
        $result = DB::table('category_pdf')
            ->where('id', $id)
            ->update(['active_status' => '2']);

        if ($result) {
            $response['status'] = "success";
            $response['message'] = "Category has been deleted successfully.";
        } else {
            $response['status'] = "fail";
            $response['message'] = "Category has been not deleted successfully.";
        }

        return response()->json($response);
    }

    /**
     * Manage centers
     */
    public function centers()
    {
        $centers = ArchiveCenter::orderBy('description')->get();
        
        return view('archive.admin.centers', compact('centers'));
    }

    /**
     * Store new center
     */
    public function storeCenter(Request $request)
    {
        $request->validate([
            'center_code' => 'required|string|max:255|unique:archive_centers,center_code',
            'description' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'status' => 'boolean'
        ]);

        ArchiveCenter::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Center created successfully'
        ]);
    }

    /**
     * Update center
     */
    public function updateCenter(Request $request, $id)
    {
        $center = ArchiveCenter::findOrFail($id);
        
        $request->validate([
            'center_code' => 'required|string|max:255|unique:archive_centers,center_code,' . $id,
            'description' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'status' => 'boolean'
        ]);

        $center->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Center updated successfully'
        ]);
    }

    /**
     * Delete center
     */
    public function deleteCenter($id)
    {
        $center = ArchiveCenter::findOrFail($id);
        
        // Check if center has archives or users
        if ($center->archives()->count() > 0 || $center->logins()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete center that has archives or users'
            ], 400);
        }

        $center->delete();

        return response()->json([
            'success' => true,
            'message' => 'Center deleted successfully'
        ]);
    }

    /**
     * Manage users
     */
    public function users()
    {
        $users = ArchiveLogin::with('center')->orderBy('uname')->get();
        $centers = ArchiveCenter::active()->orderBy('description')->get();
        
        return view('archive.admin.users', compact('users', 'centers'));
    }

    /**
     * Update user status
     */
    public function updateUserStatus(Request $request, $id)
    {
        $user = ArchiveLogin::findOrFail($id);
        
        $request->validate([
            'status' => 'required|boolean'
        ]);

        $user->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully'
        ]);
    }

    /**
     * View login logs
     */
    public function loginLogs(Request $request)
    {
        $query = ArchiveLoginLog::with(['login', 'center']);

        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        if ($request->filled('center')) {
            $query->where('center', $request->center);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('login_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('login_time', '<=', $request->end_date);
        }

        $logs = $query->orderBy('login_time', 'desc')->paginate(50);
        $centers = ArchiveCenter::active()->orderBy('description')->get();

        return view('archive.admin.login_logs', compact('logs', 'centers'));
    }

    /**
     * Archive management
     */
    public function archives(Request $request)
    {
        $query = PdfArchive::with(['center', 'category']);

        // Apply filters
        if ($request->filled('center')) {
            $query->byCenter($request->center);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('upload_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('upload_date', '<=', $request->end_date);
        }

        $archives = $query->orderBy('upload_date', 'desc')->paginate(50);
        $centers = ArchiveCenter::active()->orderBy('description')->get();
        $categories = ArchiveCategory::active()->orderBy('name')->get();

        return view('archive.admin.archives', compact('archives', 'centers', 'categories'));
    }

    /**
     * Update archive status
     */
    public function updateArchiveStatus(Request $request, $id)
    {
        $archive = PdfArchive::findOrFail($id);
        
        $request->validate([
            'status' => 'required|integer|in:0,1'
        ]);

        $archive->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Archive status updated successfully'
        ]);
    }


    /**
     * Get statistics for charts
     */
    public function getStatistics(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year
        
        switch ($period) {
            case 'day':
                $data = PdfArchive::select(DB::raw('DATE(upload_date) as date'), DB::raw('count(*) as count'))
                    ->where('upload_date', '>=', now()->subDays(30))
                    ->groupBy(DB::raw('DATE(upload_date)'))
                    ->orderBy('date')
                    ->get();
                break;
                
            case 'week':
                $data = PdfArchive::select(DB::raw('YEARWEEK(upload_date) as week'), DB::raw('count(*) as count'))
                    ->where('upload_date', '>=', now()->subWeeks(12))
                    ->groupBy(DB::raw('YEARWEEK(upload_date)'))
                    ->orderBy('week')
                    ->get();
                break;
                
            case 'year':
                $data = PdfArchive::select(DB::raw('YEAR(upload_date) as year'), DB::raw('count(*) as count'))
                    ->where('upload_date', '>=', now()->subYears(5))
                    ->groupBy(DB::raw('YEAR(upload_date)'))
                    ->orderBy('year')
                    ->get();
                break;
                
            default: // month
                $data = PdfArchive::select(DB::raw('YEAR(upload_date) as year, MONTH(upload_date) as month'), DB::raw('count(*) as count'))
                    ->where('upload_date', '>=', now()->subMonths(12))
                    ->groupBy(DB::raw('YEAR(upload_date), MONTH(upload_date)'))
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();
        }

        return response()->json($data);
    }

    /**
     * Special Dates Management
     */
    public function specialDates()
    {
        return view('archive.admin.special-dates');
    }

    public function upload()
    {
        // Return simple static view without database fetching
        return view('archive.admin.upload');
    }

    public function getCenterEditions(Request $request)
    {
        try {
            $center_id = $request->input('center_id');
            
            if (!$center_id) {
                return response()->json(['matrix_edition_details' => []]);
            }

            // Connect to matrix database to get editions
            $matrix_cn = mysqli_connect(
                config('database.connections.centers.host'),
                config('database.connections.centers.username'),
                config('database.connections.centers.password'),
                'matrix' // Different database name for editions
            );

            if (!$matrix_cn) {
                throw new \Exception('Matrix database connection failed');
            }

            $sql = "SELECT EDITIONCODE, DESCRIPTION FROM editions WHERE CENTERCODE = ? AND active_status = 1 ORDER BY DESCRIPTION";
            $stmt = mysqli_prepare($matrix_cn, $sql);
            mysqli_stmt_bind_param($stmt, 's', $center_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            $matrix_edition_details = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $matrix_edition_details[] = $row;
            }

            mysqli_close($matrix_cn);

            return response()->json(['matrix_edition_details' => $matrix_edition_details]);
        } catch (\Exception $e) {
            Log::error('Error in getCenterEditions: ' . $e->getMessage());
            return response()->json(['matrix_edition_details' => []]);
        }
    }

    public function storeUpload(Request $request)
    {
        try {
            $response = 'fail';
            
            $is_matrix_edition = $request->input('is_matrix_edition', '');
            $category = $request->input('category', '');
            $center = $request->input('center', '');
            $auto = 0;
            $edition = '';
            $edition_code = 0;
            $edition_name = '';

            // Handle Matrix Auto category
            if ($category == "Matrix Auto") {
                $auto = 1;
                $edition = $request->input('ename', '');
                if (!empty($edition)) {
                    $enamearray = explode("~", $edition);
                    $edition_code = $enamearray[0];
                    $edition_name = $enamearray[1];
                }
            } else {
                $edition_name = $request->input('txt_ename', '');
            }

            $pno = $request->input('pno', 0);
            $title = $request->input('title', '');
            $event = $request->input('event', '');
            $pdate = $request->input('pdate', '');
            
            // Convert date format from dd/mm/yyyy to yyyy-mm-dd
            if (!empty($pdate)) {
                $pdate = \DateTime::createFromFormat('d/m/Y', $pdate);
                if ($pdate) {
                    $pdate = $pdate->format('Y-m-d');
                } else {
                    throw new \Exception('Invalid date format');
                }
            }

            $userid = auth()->user()->username ?? 'admin';

            // Get uploaded files
            $files = $request->file('files', []);

            if (!empty($files)) {
                foreach ($files as $file) {
                    if ($file->isValid()) {
                        // Process file name for Matrix Auto category
                        if ($is_matrix_edition == 1 && $category == "Matrix Auto") {
                            // File name parsing logic from mypdfarchive
                            $filename = $file->getClientOriginalName();
                            $fname_array = explode("_", $filename);
                            $fname_count = count($fname_array);
                            
                            if ($fname_count <= 2) {
                                $fname = $filename;
                                $fname = str_replace("Mandideep-JJ","Mandideep*JJ",$fname);
                                $str = explode("-",$fname);
                                $str_len = count($str);
                                
                                if ($str_len > 1) {
                                    if (isset($str[0])) { 
                                        $val1 = substr($str[0],0,2); 
                                        if (is_numeric($val1)) {  
                                            $edition_name = substr($str[0],2,strlen($str[0]));  
                                        } else { 
                                            $edition_name = substr($str[0],1,strlen($str[0]));  
                                        }
                                    }
                                    if (isset($str[1])) {
                                        $pno = substr($str[1],2,strlen($str[0]));
                                        if(empty($pno)) {
                                            $pno = $str[2];
                                        }
                                    }
                                } else {
                                    $cpm_array = explode("_",$fname);
                                    $edition_name = $cpm_array[1];
                                    $pno = $cpm_array[3];
                                }
                                $edition_name = str_replace("Mandideep*JJ","Mandideep-JJ",$edition_name);
                            }

                            $title = $edition_name." Page ".$pno;
                            $event = $edition_name." Page ".$pno;
                        }

                        // Generate file paths
                        $originalFilename = $file->getClientOriginalName();
                        $fileExtension = $file->getClientOriginalExtension();
                        $filenameWithoutExt = pathinfo($originalFilename, PATHINFO_FILENAME);
                        
                        // Create storage path based on date
                        $datePath = date("dmy", strtotime($pdate));
                        $storagePath = "PDFArchive/pdf/{$datePath}/";
                        
                        if ($category == "Matrix Auto") {
                            $storagePath .= $edition_name . "/";
                        }
                        
                        // Store the file
                        $filePath = $file->storeAs($storagePath, $originalFilename, 'public');
                        
                        // Generate download URL
                        $downloadUrl = asset('storage/' . $filePath);
                        
                        // Check if record already exists
                        $existingRecord = DB::table('pdf')
                            ->where('filename', $originalFilename)
                            ->where('published_date', $pdate)
                            ->where('published_center', $center)
                            ->where('auto', $auto)
                            ->first();

                        if (!$existingRecord) {
                            // Insert new record
                            $insertId = DB::table('pdf')->insertGetId([
                                'filename' => $originalFilename,
                                'filepath' => $filePath,
                                'download_url' => $downloadUrl,
                                'title' => $title,
                                'category' => $category,
                                'event' => $event,
                                'edition_name' => $edition_name,
                                'edition_code' => $edition_code,
                                'edition_pageno' => $pno,
                                'published_date' => $pdate,
                                'published_center' => $center,
                                'upload_date' => now(),
                                'username' => $userid,
                                'auto' => $auto
                            ]);

                            if ($insertId) {
                                $response = 'success';
                            }
                        } else {
                            // Update existing record
                            DB::table('pdf')
                                ->where('id', $existingRecord->id)
                                ->update([
                                    'filepath' => $filePath,
                                    'download_url' => $downloadUrl,
                                    'edition_name' => $edition_name,
                                    'edition_code' => $edition_code,
                                    'edition_pageno' => $pno,
                                    'upload_date' => now(),
                                    'username' => $userid
                                ]);
                            $response = 'success';
                        }
                    }
                }
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error in storeUpload: ' . $e->getMessage());
            return response()->json('fail');
        }
    }

    public function display()
    {
        // Get centers from the centers database connection (same as mypdfarchive)
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

        // Get categories from the main database
        $categories = DB::table('category_pdf')->where('active_status', '1')->orderBy('category')->get();

        // Get some sample archives for display
        $archives = DB::table('pdf')->orderBy('upload_date', 'desc')->paginate(18);

        return view('archive.admin.display', compact('centers', 'categories', 'archives'));
    }

    public function searchArchives(Request $request)
    {
        try {
            // Get parameters exactly like mypdfarchive
            $center = $request->input('center', '');
            $category = $request->input('category', '');
            $pno = $request->input('pno', '');
            $startdate = $request->input('startdate', '');
            $enddate = $request->input('enddate', '');
            $page = $request->input('page', 0); // New pagination parameter
            $perPage = $request->input('per_page', 18); // Records per page

            // Convert date format from yyyy/mm/dd to yyyy-mm-dd (same as mypdfarchive)
            if (!empty($startdate)) {
                $startdate = \DateTime::createFromFormat('Y/m/d', $startdate);
                if ($startdate) {
                    $startdate = $startdate->format('Y-m-d');
                }
            }
            if (!empty($enddate)) {
                $enddate = \DateTime::createFromFormat('Y/m/d', $enddate);
                if ($enddate) {
                    $enddate = $enddate->format('Y-m-d');
                }
            }

            $query = DB::table('pdf');

            // Build query exactly like mypdfarchive
            $whereConditions = [];
            
            // Date range filter - only apply if both dates are provided
            if (!empty($startdate) && !empty($enddate)) {
                $query->whereBetween('published_date', [$startdate, $enddate]);
            }
            
            // Category filter
            if (!empty($category)) {
                $query->where('category', $category);
            }
            
            // Center filter logic (same as mypdfarchive)
            if (empty($category)) {
                if (!empty($center)) {
                    $query->where('published_center', $center);
                }
            } else {
                if (!empty($center)) {
                    $query->where('published_center', $center);
                }
            }
            
            // Page number filter
            if (!empty($pno)) {
                $query->where('edition_pageno', $pno);
            }

            // Get total count for pagination (same as SearchTotalNew)
            $totalCount = $query->count();
            
            // Get paginated results with new pagination system
            $archives = $query->orderBy('edition_name')
                            ->orderBy('edition_pageno')
                            ->offset($page * $perPage) // Calculate offset properly
                            ->limit($perPage)
                            ->get();

            
            // Prepare data for new pagination system
            $data = [];
            foreach ($archives as $archive) {
                // Generate thumbnail path
                $thumbnailPath = $this->generateThumbnailPath($archive);
                
                $data[] = [
                    'id' => $archive->id,
                    'title' => $archive->title ?: 'Page',
                    'category' => $archive->category,
                    'edition_pageno' => $archive->edition_pageno,
                    'published_center' => $archive->published_center,
                    'published_date' => $archive->published_date,
                    'upload_date' => $archive->upload_date,
                    'filepath' => $archive->filepath,
                    'thumbnail_path' => $thumbnailPath,
                    'auto' => $archive->auto,
                    'filename' => $archive->filename,
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'total_count' => $totalCount,
                'current_page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($totalCount / $perPage)
            ]);

        } catch (\Exception $e) {
            Log::error('Search archives failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 'fail',
                'message' => 'Search failed: ' . $e->getMessage()
            ]);
        }
    }

    public function searchTotal(Request $request)
    {
        try {
            $center = $request->input('center', '');
            $pno = $request->input('pno', '');
            $category = $request->input('category', '');
            $startdate = $request->input('startdate', '');
            $enddate = $request->input('enddate', '');

            // Convert date format
            if (!empty($startdate)) {
                $startdate = \DateTime::createFromFormat('Y/m/d', $startdate);
                if ($startdate) {
                    $startdate = $startdate->format('Y-m-d');
                }
            }
            if (!empty($enddate)) {
                $enddate = \DateTime::createFromFormat('Y/m/d', $enddate);
                if ($enddate) {
                    $enddate = $enddate->format('Y-m-d');
                }
            }

            // Build query
            $query = DB::table('pdf');
            
            if (!empty($center)) {
                $query->where('published_center', $center);
            }
            if (!empty($pno)) {
                $query->where('edition_pageno', $pno);
            }
            if (!empty($category)) {
                $query->where('category', $category);
            }
            if (!empty($startdate)) {
                $query->where('published_date', '>=', $startdate);
            }
            if (!empty($enddate)) {
                $query->where('published_date', '<=', $enddate);
            }

            $totalCount = $query->count();
            
            return response()->json(['total' => $totalCount]);
        } catch (\Exception $e) {
            Log::error('Error in searchTotal: ' . $e->getMessage());
            return response()->json(['total' => 0]);
        }
    }

    public function downloadLog(Request $request)
    {
        try {
            $alias = $request->input('alias');
            $download_url = $request->input('download_url');
            $date = $request->input('date');
            $ccode = $request->input('ccode');
            $edition_code = $request->input('edition_code');
            $pageno = $request->input('pageno');

            // Log download activity
            DB::table('download_logs')->insert([
                'alias' => $alias,
                'download_url' => $download_url,
                'date' => $date,
                'ccode' => $ccode,
                'edition_code' => $edition_code,
                'pageno' => $pageno,
                'user_id' => auth()->id(),
                'created_at' => now()
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in downloadLog: ' . $e->getMessage());
            return response()->json(['status' => 'error']);
        }
    }

    public function deleteArchive(Request $request)
    {
        try {
            $id = $request->input('id');
            
            $archive = DB::table('pdf')->where('id', $id)->first();
            if ($archive) {
                // Delete file if exists
                if (!empty($archive->filepath)) {
                    $filePath = storage_path('app/public/' . $archive->filepath);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                
                // Delete database record
                DB::table('pdf')->where('id', $id)->delete();
                
                return response('success');
            }
            
            return response('error');
        } catch (\Exception $e) {
            Log::error('Error in deleteArchive: ' . $e->getMessage());
            return response('error');
        }
    }

    private function generateThumbnailPath($archive)
    {
        // Generate thumbnail path using same logic as mypdfarchive
        if ($archive->filepath) {
            $path = explode("/", $archive->filepath);
            if (count($path) >= 5) {
                // Use regular thumb format like mypdfarchive
                if($archive->auto == '0'){
                    $thumbName = 'epaper-pdfarchive-live-bucket/PDFArchive/thumb/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[4]));
                } else {
                    $thumbName = 'epaper-pdfarchive-live-bucket/PDFArchive/thumb/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[5]));
                }
                return 'https://storage.googleapis.com/' . $thumbName;
            }
        }
        
        // Fallback to default image
        return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDMwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0yMjUgMTEyLjVIMTg3LjVWNzVIMjI1VjExMi41WiIgZmlsbD0iI0Q5RDlEOSIvPgo8cGF0aCBkPSJNMjI1IDExMi41SDE4Ny41Vjc1IiBzdHJva2U9IiNDQ0NDQ0MiIHN0cm9rZS13aWR0aD0iMyIvPgo8dGV4dCB4PSIxNTAiIHk9IjE5NSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIxIj5GaWxlPC90ZXh0Pgo8dGV4dCB4PSIxNTAiIHk9IjIyNSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE4Ij5Ob3QgYXZhaWxhYmxlPC90ZXh0Pgo8L3N2Zz4K';
    }

    private function generateLargeThumbnailPath($archive)
    {
        // Generate large thumbnail path using same logic as mypdfarchive
        if ($archive->filepath) {
            $path = explode("/", $archive->filepath);
            if (count($path) >= 5) {
                // Use largeThumbName format like mypdfarchive
                if($archive->auto == '0'){
                    $largeThumbName = 'epaper-pdfarchive-live-bucket/PDFArchive/thumb-large/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[4]));
                } else {
                    $largeThumbName = 'epaper-pdfarchive-live-bucket/PDFArchive/thumb-large/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[5]));
                }
                return 'https://storage.googleapis.com/' . $largeThumbName;
            }
        }
        
        // Fallback to default image
        return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDMwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0yMjUgMTEyLjVIMTg3LjVWNzVIMjI1VjExMi41WiIgZmlsbD0iI0Q5RDlEOSIvPgo8cGF0aCBkPSJNMjI1IDExMi41SDE4Ny41Vjc1IiBzdHJva2U9IiNDQ0NDQ0MiIHN0cm9rZS13aWR0aD0iMyIvPgo8dGV4dCB4PSIxNTAiIHk9IjE5NSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIxIj5GaWxlPC90ZXh0Pgo8dGV4dCB4PSIxNTAiIHk9IjIyNSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE4Ij5Ob3QgYXZhaWxhYmxlPC90ZXh0Pgo8L3N2Zz4K';
    }

    /**
     * Get special dates data for DataTables
     */
    public function getSpecialDatesData()
    {
        try {
            $specialDates = DB::table('special_dates')
                ->select(['id', 'description', 'special_date', 'active_status'])
                ->where('active_status', '1')
                ->orderBy('special_date')
                ->get();

            Log::info('Special dates found: ' . $specialDates->count());

            $data = array();
            $a = 1;
            foreach ($specialDates as $key => $specialDate) {
                $data[$key][] = $a;
                $data[$key][] = $specialDate->description;
                $data[$key][] = $specialDate->special_date;
                $data[$key][] = '<a href="javascript:void(0);" class="btn btn-warning btn-xs btn-edit" style="margin-right:10px" title="Edit" data-id="'.$specialDate->id.'"><i class="fa fa-edit"></i></a><a href="javascript:void(0);" class="btn btn-danger btn-xs btn-delete" style="margin-right:10px" title="Delete" data-id="'.$specialDate->id.'"><i class="fa fa-times"></i></a>';
                $a++;
            }

            $data_table['draw'] = 1;
            $data_table['recordsTotal'] = count($specialDates);
            $data_table['recordsFiltered'] = count($specialDates);
            $data_table['data'] = $data;

            Log::info('Special Dates DataTable response: ' . json_encode($data_table));

            return response()->json($data_table);
        } catch (\Exception $e) {
            Log::error('Error in getSpecialDatesData: ' . $e->getMessage());
            return response()->json([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store new special date
     */
    public function storeSpecialDate(Request $request)
    {
        $request->validate([
            'special_date' => 'required|string|max:20',
            'description' => 'required|string|max:255',
        ]);

        if ($request->id == '') {
            // Add new special date
            $insertId = DB::table('special_dates')->insertGetId([
                'special_date' => $request->special_date,
                'description' => $request->description,
                'active_status' => '1',
            ]);
        } else {
            // Update existing special date
            DB::table('special_dates')
                ->where('id', $request->id)
                ->update([
                    'special_date' => $request->special_date,
                    'description' => $request->description,
                ]);
            $insertId = $request->id;
        }

        if ($insertId) {
            $response['status'] = "success";
            $response['message'] = "Special date has been added successfully.";
        } else {
            $response['status'] = "fail";
            $response['message'] = "Special date has been not added successfully.";
        }
        return response()->json($response);
    }

    /**
     * Edit special date - get single record
     */
    public function editSpecialDate($id)
    {
        try {
            $specialDate = DB::table('special_dates')
                ->select(['id', 'special_date', 'description'])
                ->where('id', $id)
                ->where('active_status', '1')
                ->first();

            if ($specialDate) {
                return response()->json([
                    'id' => $specialDate->id,
                    'special_date' => $specialDate->special_date,
                    'description' => $specialDate->description,
                ]);
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Special date not found'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in editSpecialDate: ' . $e->getMessage());
            return response()->json([
                'status' => 'fail',
                'message' => 'Error loading special date data'
            ]);
        }
    }

    /**
     * Delete special date (soft delete)
     */
    public function deleteSpecialDate($id)
    {
        try {
            $result = DB::table('special_dates')
                ->where('id', $id)
                ->update(['active_status' => '2']);

            if ($result) {
                $response['status'] = "success";
                $response['message'] = "Special date has been deleted successfully.";
            } else {
                $response['status'] = "fail";
                $response['message'] = "Special date has been not deleted successfully.";
            }
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error in deleteSpecialDate: ' . $e->getMessage());
            return response()->json([
                'status' => 'fail',
                'message' => 'Error deleting special date'
            ]);
        }
    }
}
