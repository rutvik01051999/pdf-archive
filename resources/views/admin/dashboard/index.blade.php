@extends('admin.layouts.app')

@section('content')
    <!-- Page Header -->
    @include('admin.layouts.partials.page-header', [
        'title' => 'Dashboard',
        'breadcrumb' => [
            'Home' => route('admin.dashboard.index'),
        ],
    ])

    <!-- Welcome Message -->
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-file-blank fs-48 text-primary mb-3"></i>
                    <h4 class="fw-semibold mb-2">Welcome to PDF Archive Management</h4>
                    <p class="text-muted mb-4">Manage your PDF archives efficiently with our comprehensive admin panel.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('admin.archive.upload') }}" class="btn btn-primary">
                            <i class="bx bx-upload me-2"></i>Upload Archives
                        </a>
                        <a href="{{ route('admin.archive.display') }}" class="btn btn-outline-primary">
                            <i class="bx bx-search me-2"></i>Browse Archives
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

