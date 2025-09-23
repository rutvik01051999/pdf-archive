@extends('admin.layouts.app')

@section('content')
<!-- Page Header -->
@include('admin.layouts.partials.page-header', [
    'title' => 'Archive Dashboard',
    'breadcrumb' => [
        'Home' => route('admin.dashboard.index'),
        'Archive Dashboard' => route('admin.archive.dashboard'),
    ],
])

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar bg-primary">
                            <i class="bx bx-file-blank fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-semibold text-muted d-block mb-1">Total Archives</span>
                                <h4 class="fw-semibold mb-0">{{ $stats['total_archives'] }}</h4>
                            </div>
                            <div class="text-end">
                                <small class="text-success">
                                    <i class="bx bx-up-arrow-alt"></i>
                                    {{ $stats['active_archives'] }} active
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar bg-success">
                            <i class="bx bx-user fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-semibold text-muted d-block mb-1">Total Users</span>
                                <h4 class="fw-semibold mb-0">{{ $stats['total_users'] }}</h4>
                            </div>
                            <div class="text-end">
                                <small class="text-info">
                                    <i class="bx bx-user-check"></i>
                                    {{ $stats['active_users'] }} active
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar bg-warning">
                            <i class="bx bx-building fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-semibold text-muted d-block mb-1">Centers</span>
                                <h4 class="fw-semibold mb-0">{{ $stats['total_centers'] }}</h4>
                            </div>
                            <div class="text-end">
                                <small class="text-success">
                                    <i class="bx bx-layer"></i>
                                    {{ $stats['total_categories'] }} categories
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar bg-info">
                            <i class="bx bx-upload fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-semibold text-muted d-block mb-1">Today's Uploads</span>
                                <h4 class="fw-semibold mb-0">{{ $stats['today_uploads'] }}</h4>
                            </div>
                            <div class="text-end">
                                <small class="text-primary">
                                    <i class="bx bx-calendar"></i>
                                    {{ $stats['this_month_uploads'] }} this month
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <!-- Recent Uploads -->
    <div class="col-xl-6 col-lg-12">
        <div class="card custom-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title">
                    <h6 class="mb-0">Recent Uploads</h6>
                </div>
                <a href="{{ route('admin.archive.archives') }}" class="btn btn-sm btn-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Center</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUploads as $upload)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($upload->title, 20) }}</strong>
                                        @if($upload->is_matrix_edition)
                                            <span class="badge bg-primary ms-1">M</span>
                                        @endif
                                    </td>
                                    <td>{{ $upload->center }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $upload->category }}</span>
                                    </td>
                                    <td>{{ $upload->upload_date->format('d M Y') }}</td>
                                    <td>{{ $upload->file_size_formatted }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent uploads found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Logins -->
    <div class="col-xl-6 col-lg-12">
        <div class="card custom-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title">
                    <h6 class="mb-0">Recent Logins</h6>
                </div>
                <a href="{{ route('admin.archive.login-logs') }}" class="btn btn-sm btn-success">
                    View All
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Center</th>
                                <th>IP Address</th>
                                <th>Login Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLogins as $login)
                                <tr>
                                    <td><strong>{{ $login->username }}</strong></td>
                                    <td>{{ $login->center }}</td>
                                    <td>{{ $login->ip_address ?: 'N/A' }}</td>
                                    <td>{{ $login->login_time->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-success">Success</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent logins found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="me-3">
                                <i class="bx bx-list-ul fs-24 text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Manage Archives</h6>
                                <p class="text-muted mb-0">View and manage all archives</p>
                                <a href="{{ route('admin.archive.archives') }}" class="btn btn-sm btn-outline-primary mt-2">Go to Archives</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="me-3">
                                <i class="bx bx-category fs-24 text-success"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Categories</h6>
                                <p class="text-muted mb-0">Manage archive categories</p>
                                <a href="{{ route('admin.archive.categories') }}" class="btn btn-sm btn-outline-success mt-2">Manage Categories</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="me-3">
                                <i class="bx bx-building fs-24 text-info"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Centers</h6>
                                <p class="text-muted mb-0">Manage archive centers</p>
                                <a href="{{ route('admin.archive.centers') }}" class="btn btn-sm btn-outline-info mt-2">Manage Centers</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="me-3">
                                <i class="bx bx-user fs-24 text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Users</h6>
                                <p class="text-muted mb-0">Manage archive users</p>
                                <a href="{{ route('admin.archive.users') }}" class="btn btn-sm btn-outline-warning mt-2">Manage Users</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

