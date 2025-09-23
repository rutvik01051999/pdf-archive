@extends('admin.layouts.app')

@section('content')
    @include('admin.layouts.partials.page-header', [
        'title' => 'Employee Details',
        'breadcrumb' => [
            'Home' => route('admin.dashboard.index'),
            'Employee Management' => route('admin.employees.index'),
            'Employee Details' => '#'
        ]
    ])

    @include('admin.layouts.partials.alert')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title">Employee Information - {{ $employee->full_name }}</h5>
                    <div class="d-flex">
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-secondary me-2">
                            <i class="bx bx-arrow-back"></i> Back to List
                        </a>
                        <a href="{{ route('admin.employees.create') }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-plus"></i> Add New Employee
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Employee ID:</strong>
                                <p class="card-text">
                                    <span class="badge bg-primary fs-6">{{ $employee->username }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Full Name:</strong>
                                <p class="card-text">{{ $employee->full_name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Email Address:</strong>
                                <p class="card-text">
                                    <a href="mailto:{{ $employee->email }}" class="text-decoration-none">
                                        <i class="bx bx-envelope"></i> {{ $employee->email }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Phone Number:</strong>
                                <p class="card-text">
                                    <a href="tel:{{ $employee->mobile_number }}" class="text-decoration-none">
                                        <i class="bx bx-phone"></i> {{ $employee->mobile_number }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Department:</strong>
                                <p class="card-text">
                                    <span class="badge bg-info fs-6">{{ $employee->department }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <p class="card-text">
                                    @if($employee->status->value === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($employee->status->value === 'inactive')
                                        <span class="badge bg-danger">Inactive</span>
                                    @else
                                        <span class="badge bg-warning">Suspended</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Created At:</strong>
                                <p class="card-text">
                                    <i class="bx bx-calendar"></i> {{ $employee->created_at->format('d M Y H:i:s') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Last Updated:</strong>
                                <p class="card-text">
                                    <i class="bx bx-time"></i> {{ $employee->updated_at->format('d M Y H:i:s') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
