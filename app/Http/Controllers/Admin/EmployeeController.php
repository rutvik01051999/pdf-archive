<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmployeeApiService;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    protected $employeeApiService;

    public function __construct(EmployeeApiService $employeeApiService)
    {
        $this->employeeApiService = $employeeApiService;
    }

    /**
     * Display a listing of employees (users with Admin role)
     */
    public function index()
    {
        // Employee management disabled - return empty collection
        $employees = collect([]);
        return view('admin.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new employee (disabled)
     */
    public function create()
    {
        return redirect()->route('admin.employees.index')
            ->with('error', 'Employee management is currently disabled.');
    }

    /**
     * Store a newly created employee (disabled)
     */
    public function store(Request $request)
    {
        return redirect()->route('admin.employees.index')
            ->with('error', 'Employee management is currently disabled.');
    }

    /**
     * Fetch employee data from external API
     */
    public function fetchEmployeeData(Request $request)
    {
        $request->validate([
            'alias' => 'required|string|max:255'
        ]);

        $alias = $request->input('alias');
        
        // Fetch data from external API
        $apiResult = $this->employeeApiService->getEmployeeData($alias);
        
        if (!$apiResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $apiResult['message'] ?? 'Failed to fetch employee data'
            ], 400);
        }

        // Parse the API response
        $parsedResult = $this->employeeApiService->parseEmployeeData($apiResult['data']);
        
        if (!$parsedResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $parsedResult['message'] ?? 'Failed to parse employee data'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $parsedResult['data']
        ]);
    }

    /**
     * Display the specified employee (disabled)
     */
    public function show(User $employee)
    {
        return redirect()->route('admin.employees.index')
            ->with('error', 'Employee management is currently disabled.');
    }

    /**
     * Remove the specified employee from storage
     */
    public function destroy(User $employee)
    {
        return redirect()->route('admin.employees.index')
            ->with('error', 'Employee management is currently disabled.');
    }
}
