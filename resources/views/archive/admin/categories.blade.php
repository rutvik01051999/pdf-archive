@extends('admin.layouts.app')

@section('content')
    @include('admin.layouts.partials.page-header', [
        'title' => 'Archive Categories',
        'breadcrumb' => [
            'Home' => route('admin.dashboard.index'),
            'Archive' => route('admin.archive.dashboard'),
            'Categories' => route('admin.archive.categories'),
        ]
    ])

    @include('admin.layouts.partials.alert')

    <!-- Category Form -->
    <div class="row" id="form" style="display: none;">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bx bx-file me-2"></i>
                        Category Form
                    </div>
                </div>
                <div class="card-body">
                    <form id="defaultForm" method="post">
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="mb-3">
                                    <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <input type="hidden" name="id" id="id">
                                    <input type="text" class="form-control" id="category_name" name="category_name" placeholder="Enter category name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-12">
                                <button type="button" class="btn btn-primary" id="submit_Bnews">
                                    <i class="bx bx-check me-1"></i> Submit
                                </button>
                                <button type="reset" class="btn btn-light" id="cancel">
                                    <i class="bx bx-x me-1"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="row" id="dataTable">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title">
                        <i class="bx bx-list me-2"></i>
                        Category List
                    </div>
                    <button type="button" class="btn btn-primary" id="addNew">
                        <i class="bx bx-plus me-1"></i> Add New Category
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap w-100" id="dataTableId">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Categories JavaScript -->
<script src="{{ asset('assets/js/categories.js') }}"></script>
<script>
// Set up URLs for the categories functionality
window.categoriesDataUrl = '{{ route("admin.archive.categories.data") }}';
window.categoriesStoreUrl = '{{ route("admin.archive.categories.store") }}';
window.categoriesDeleteUrl = '{{ route("admin.archive.categories.delete", ":id") }}';
window.categoriesEditUrl = '{{ route("admin.archive.categories.edit", ":id") }}';
</script>
@endpush

