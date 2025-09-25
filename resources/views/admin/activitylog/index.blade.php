@extends('admin.layouts.app')

@section('content')
    <!-- Page Header -->
    @include('admin.layouts.partials.page-header', [
        'title' => 'Activity Logs',
        'breadcrumb' => [
            'Home' => route('admin.dashboard.index'),
            'Activity Logs' => '#',
        ],
    ])

    @include('admin.layouts.partials.alert')

    <!-- Activity Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="avatar bg-primary">
                                <i class="bx bx-activity fs-18"></i>
                            </span>
                        </div>
                        <div class="flex-fill">
                            <span class="fw-semibold text-muted d-block mb-1">Total Activities</span>
                            <h4 class="fw-semibold mb-0">{{ $activityStats['total_activities'] ?? 0 }}</h4>
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
                                <i class="bx bx-search fs-18"></i>
                            </span>
                        </div>
                        <div class="flex-fill">
                            <span class="fw-semibold text-muted d-block mb-1">Archive Searches</span>
                            <h4 class="fw-semibold mb-0">{{ $activityStats['archive_searches'] ?? 0 }}</h4>
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
                                <i class="bx bx-upload fs-18"></i>
                            </span>
                        </div>
                        <div class="flex-fill">
                            <span class="fw-semibold text-muted d-block mb-1">Archive Uploads</span>
                            <h4 class="fw-semibold mb-0">{{ $activityStats['archive_uploads'] ?? 0 }}</h4>
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
                                <i class="bx bx-download fs-18"></i>
                            </span>
                        </div>
                        <div class="flex-fill">
                            <span class="fw-semibold text-muted d-block mb-1">PDF Downloads</span>
                            <h4 class="fw-semibold mb-0">{{ $activityStats['pdf_downloads'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title">
                        <i class="bx bx-history me-2"></i>
                        Activity Logs
                    </div>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                            <i class="bx bx-refresh me-1"></i>
                            Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table([
                            'class' => 'w-100 table-striped table-hover table-bordered',
                        ]) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    
    <script>
        function refreshTable() {
            $('#activitylog-table').DataTable().ajax.reload();
        }
    </script>
@endpush

