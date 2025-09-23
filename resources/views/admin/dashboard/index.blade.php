@extends('admin.layouts.app')

@section('content')
    <!-- Page Header -->
    @include('admin.layouts.partials.page-header', [
        'title' => 'Dashboard',
        'breadcrumb' => [
            'Home' => route('admin.dashboard.index'),
        ],
    ])

    <!-- Employee Management Statistics -->
    <div class="row">
        <!-- Employee Management -->
        <div class="col-xl-6 col-lg-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="avatar bg-primary">
                                <i class="bx bx-user-plus fs-18"></i>
                            </span>
                        </div>
                        <div class="flex-fill">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fw-semibold text-muted d-block mb-1">Total Employees</span>
                                    <h4 class="fw-semibold mb-0">{{ $moduleStats['employees']['total'] ?? 0 }}</h4>
                                </div>
                                <div class="text-end">
                                    <small class="text-success">
                                        <i class="bx bx-up-arrow-alt"></i>
                                        {{ $moduleStats['employees']['today'] ?? 0 }} today
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Management Actions -->
        <div class="col-xl-6 col-lg-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="avatar bg-success">
                                <i class="bx bx-cog fs-18"></i>
                            </span>
                        </div>
                        <div class="flex-fill">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fw-semibold text-muted d-block mb-1">Quick Actions</span>
                                    <h6 class="fw-semibold mb-0">Manage Employees</h6>
                                </div>
                                <div class="text-end">
                                    <a href="{{ route('admin.employees.create') }}" class="btn btn-sm btn-primary">
                                        Add Employee
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Management Details -->
    <div class="row">
        <!-- Employee Details -->
        <div class="col-xl-12 col-lg-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        <h6 class="mb-0">Employee Management Overview</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Total Employees</span>
                                <span class="fw-semibold text-success">{{ $moduleStats['employees']['total'] ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Added Today</span>
                                <span class="fw-semibold text-primary">{{ $moduleStats['employees']['today'] ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">This Month</span>
                                <span class="fw-semibold text-info">{{ $moduleStats['employees']['this_month'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Management Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title">
                        <h6 class="mb-0">Employee Management</h6>
                    </div>
                    <div>
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-primary me-2">
                            View All Employees
                        </a>
                        <a href="{{ route('admin.employees.create') }}" class="btn btn-sm btn-success">
                            Add New Employee
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="me-3">
                                    <i class="bx bx-list-ul fs-24 text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">View Employees</h6>
                                    <p class="text-muted mb-0">Browse and manage all employees</p>
                                    <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-primary mt-2">Go to List</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="me-3">
                                    <i class="bx bx-user-plus fs-24 text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Add Employee</h6>
                                    <p class="text-muted mb-0">Create a new employee account</p>
                                    <a href="{{ route('admin.employees.create') }}" class="btn btn-sm btn-outline-success mt-2">Add Employee</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

