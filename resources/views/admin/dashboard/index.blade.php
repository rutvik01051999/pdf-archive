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

    <!-- Archive Search Form -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bx bx-search me-2"></i>
                        Archive Search & Filter
                    </div>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="resetFilters">
                            <i class="bx bx-refresh me-1"></i> Reset
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form name="frmSearch" method="post" id="archiveSearchForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="center" class="form-label">Center</label>
                                <select class="form-select center" name="center" id="center">
                                    <option value="">All Centers</option>
                                    @foreach($archiveData['centers'] as $center)
                                    <option value="{{ $center->centercode }}">{{ $center->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="pno" class="form-label">Page No</label>
                                <select class="form-select" name="pno" id="pno">
                                    <option value="">All Pages</option>
                                    @for($i = 1; $i <= 100; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" name="category" id="category">
                                    <option value="">All Categories</option>
                                    @foreach($archiveData['categories'] as $category)
                                    <option value="{{ $category->category }}">{{ $category->category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="startdate" class="form-label">From Date</label>
                                <input class="form-control" type="date" placeholder="From Date" value="{{ date('Y-m-d') }}" name="startdate" id="startdate">
                            </div>
                            <div class="col-md-2">
                                <label for="enddate" class="form-label">To Date</label>
                                <input class="form-control" type="date" id="enddate" placeholder="To Date" value="{{ date('Y-m-d') }}" name="enddate">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="button" class="btn btn-primary SearchForm" name="go">
                                        <i class="bx bx-search me-1"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Search Results -->
    <div class="row" id="ArchiveSearchRes">
        <!-- Search results will be displayed here -->
    </div>

    <!-- Tab Structure for Editions -->
    <div class="row" id="ResultFilter" style="display: none;">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <div id="edition_tabs">
                    <!-- Tab headers will be populated here -->
                </div>
            </div>
            <div id="edition_tabs_content" class="tab-content">
                <!-- Tab content will be populated here -->
            </div>
        </div>
    </div>

    <!-- Archive Results Grid -->
    <div class="row" id="ResultNew">
        @if($archiveData['archives']->count() > 0)
        @foreach($archiveData['archives'] as $archive)
        <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-4">
            <div class="archive-item">
                <div class="archive-thumbnail-container">
                    <img src="{{ $archive->thumbnail_path ?: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDMwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0yMjUgMTEyLjVIMTg3LjVWNzVIMjI1VjExMi41WiIgZmlsbD0iI0Q5RDlEOSIvPgo8cGF0aCBkPSJNMjI1IDExMi41SDE4Ny41Vjc1IiBzdHJva2U9IiNDQ0NDQ0MiIHN0cm9rZS13aWR0aD0iMyIvPgo8dGV4dCB4PSIxNTAiIHk9IjE5NSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIxIj5GaWxlPC90ZXh0Pgo8dGV4dCB4PSIxNTAiIHk9IjIyNSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE4Ij5Ob3QgYXZhaWxhYmxlPC90ZXh0Pgo8L3N2Zz4K' }}"
                        class="archive-thumbnail"
                        alt="{{ $archive->title ?: 'Archive' }}"
                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDMwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0yMjUgMTEyLjVIMTg3LjVWNzVIMjI1VjExMi41WiIgZmlsbD0iI0Q5RDlEOSIvPgo8cGF0aCBkPSJNMjI1IDExMi41SDE4Ny41Vjc1IiBzdHJva2U9IiNDQ0NDQ0MiIHN0cm9rZS13aWR0aD0iMyIvPgo8dGV4dCB4PSIxNTAiIHk9IjE5NSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIxIj5GaWxlPC90ZXh0Pgo8dGV4dCB4PSIxNTAiIHk9IjIyNSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE4Ij5Ob3QgYXZhaWxhYmxlPC90ZXh0Pgo8L3N2Zz4K'">
                </div>
                <div class="archive-info">
                    <div class="archive-page">Page {{ $archive->edition_pageno ?? 'N/A' }}</div>
                    <div class="archive-category">{{ $archive->category ?? 'N/A' }}</div>
                </div>
                <div class="archive-actions">
                    <a href="{{ route('admin.archive.edit', $archive->id) }}" target="_blank" class="btn btn-icon" title="Edit"><i class="bx bx-edit"></i></a>
                    <button class="btn btn-icon" onclick="confirmation({{ $archive->id }})" title="Delete"><i class="bx bx-trash"></i></button>
                    <a href="{{ route('admin.archive.copy', $archive->id) }}" target="_blank" class="btn btn-icon" title="Copy"><i class="bx bx-copy"></i></a>
                    <a href="{{ $archive->pdf_url }}" target="_blank" class="btn btn-icon" title="Download" onclick="download_log('{{ auth()->user()?->username ?? 'admin' }}','{{ $archive->download_url }}','{{ $archive->published_date }}','{{ $archive->published_center }}','{{ $archive->edition_code }}','{{ $archive->edition_pageno }}')"><i class="bx bx-download"></i></a>
                    <a href="{{ $archive->pdf_url }}" target="_blank" class="btn btn-icon" title="Print"><i class="bx bx-printer"></i></a>
                </div>
            </div>
        </div>
        @endforeach
        @else
        <div class="col-12 text-center">
            <div class="alert alert-info">
                <i class="bx bx-info-circle me-2"></i>
                No archives found. Upload some PDFs to get started.
            </div>
        </div>
        @endif
    </div>

    <!-- Pagination -->
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="pagination-info">
                        <small class="text-muted">
                            <span id="TotalVisibleArchive">Showing 0 - </span>
                            <span id="TotalArchive">0</span>
                        </small>
                    </div>
                    <div class="pagination-controls">
                        <button class="btn btn-outline-primary btn-sm PreviousPagination" type="button">
                            <i class="bx bx-chevron-left"></i> Previous
                        </button>
                        <button class="btn btn-outline-primary btn-sm NextPagination" type="button">
                            Next <i class="bx bx-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global variables for search
var currentPage = 0;
var totalRecords = 0;
var recordsPerPage = 18;
var totalPages = 0;

$(document).ready(function(){
    // Search functionality
    $('.SearchForm').click(function(){
        currentPage = 0;
        loadArchives(0);
    });

    // Pagination handlers
    $('.PreviousPagination').click(function(){
        if(currentPage > 0) {
            currentPage--;
            loadArchives(currentPage);
        }
    });

    $('.NextPagination').click(function(){
        if(currentPage < totalPages - 1) {
            currentPage++;
            loadArchives(currentPage);
        }
    });

    // Reset filters
    $('#resetFilters').click(function() {
        $('#archiveSearchForm')[0].reset();
        $('#startdate').val(''); // Clear start date
        $('#enddate').val(''); // Clear end date
        currentPage = 0;
        loadArchives(0);
    });

    // Set default date values
    $('#startdate').val('{{ date("Y-m-d") }}');
    $('#enddate').val('{{ date("Y-m-d") }}');
    
    // Load initial results
    loadArchives(0);
});

// Load archives via AJAX
function loadArchives(page) {
    var formData = $('#archiveSearchForm').serializeArray();
    formData.push({name: 'page', value: page});
    formData.push({name: 'per_page', value: recordsPerPage});
    
    $.ajax({
        url: '{{ route("admin.archive.search") }}',
        type: 'POST',
        data: formData,
        success: function(response) {
            if(response.success) {
                // Check if we have tab data (center selected, no category)
                if(response.str_tab_html && response.str_tab_div_html) {
                    displayTabs(response);
                } else {
                    // Regular search results - use HTML content
                    displayRegularResults(response);
                    updatePagination(response);
                }
            } else {
                showNoResults();
            }
        },
        error: function(xhr) {
            console.error('Search failed:', xhr);
            showError();
        }
    });
}

// Display tabs for editions
function displayTabs(response) {
    // Hide regular search results
    $('#ArchiveSearchRes').hide();
    $('.cls-div-pagination').hide();
    $('#ResultFilter').show();
    
    // Populate tab headers
    $('#edition_tabs').html(response.str_tab_html);
    $('#edition_tabs_content').html(response.str_tab_div_html);
    
    // Activate first tab
    $('.edition-tab:first').addClass('active');
}

// Display regular search results (HTML content)
function displayRegularResults(response) {
    // Hide tabs and show regular results
    $('#ResultFilter').hide();
    $('#ArchiveSearchRes').show();
    $('.cls-div-pagination').show();
    
    if(response.html) {
        $('#ArchiveSearchRes').html(response.html);
    } else {
        showNoResults();
    }
}

// Update pagination controls
function updatePagination(response) {
    totalRecords = response.total_count;
    totalPages = Math.ceil(totalRecords / recordsPerPage);
    
    var startRecord = (currentPage * recordsPerPage) + 1;
    var endRecord = Math.min((currentPage + 1) * recordsPerPage, totalRecords);
    
    // Update pagination info
    $('#TotalVisibleArchive').text('Showing ' + startRecord + ' - ' + endRecord + ' of ');
    $('#TotalArchive').text(totalRecords);
    
    // Update pagination buttons
    $('.PreviousPagination').prop('disabled', currentPage === 0);
    $('.NextPagination').prop('disabled', currentPage >= totalPages - 1);
}

// Show no results message
function showNoResults() {
    var html = '<div class="col-12 text-center py-5">';
    html += '<i class="bx bx-inbox bx-lg text-muted mb-3"></i>';
    html += '<h5 class="text-muted">No archives found</h5>';
    html += '<p class="text-muted">Try adjusting your search criteria.</p>';
    html += '</div>';
    $('#ArchiveSearchRes').html(html);
}

// Show error message
function showError() {
    var html = '<div class="col-12 text-center py-5">';
    html += '<i class="bx bx-error bx-lg text-danger mb-3"></i>';
    html += '<h5 class="text-danger">Error loading archives</h5>';
    html += '<p class="text-muted">Please try again later.</p>';
    html += '</div>';
    $('#ArchiveSearchRes').html(html);
}

// Archive management functions
function confirmation(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        background: '#fff',
        customClass: {
            popup: 'swal2-popup-custom',
            title: 'swal2-title-custom',
            content: 'swal2-content-custom'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we delete the archive.',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Delete archive via AJAX
            $.ajax({
                url: '{{ route("admin.archive.delete") }}',
                type: 'POST',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'id': id
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Archive has been deleted successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page or refresh results
                        location.reload();
                    });
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete archive.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function download_log(alias, download_url, date, ccode, edition_code, pageno) {
    $.ajax({
        type:'POST',
        data: { 
            '_token': '{{ csrf_token() }}',
            'alias': alias, 
            'download_url': download_url, 
            'date': date, 
            'ccode': ccode, 
            'edition_code': edition_code, 
            'pageno': pageno
        },
        url:'{{ route("admin.archive.download-log") }}',
        success:function(response){
            console.log("download log recorded");
        },
        error: function(xhr) {
            console.error('Download log failed:', xhr);
        }
    });
}

// Tab functionality functions
var editionStartPage = 0;
var editionCurrentPage = 0;
var isEditionChange = 0;

function fnGetTabData(edition_code) {
    console.log('Loading tab data for edition:', edition_code);
    
    // Update active tab button
    $('.edition-tab').removeClass('active');
    $('.edition-tab[data-edition="' + edition_code + '"]').addClass('active');
    
    // Clear current content and show loading
    $('#edition_tabs_content').html('<div class="col-12 text-center py-5"><i class="bx bx-loader-alt bx-spin bx-lg text-primary mb-3"></i><h5 class="text-muted">Loading...</h5></div>');
    
    var formData = $('#archiveSearchForm').serializeArray();
    formData.push({name: 'edition_code', value: edition_code});
    formData.push({name: 'pages', value: 0});
    
    editionStartPage = 0;
    $.ajax({
        type: 'POST',
        data: formData,
        url: '{{ route("admin.archive.search") }}',
        success: function(response){
            if(response.success && response.str_tab_div_html) {
                // Wrap content in row div and put in edition_tabs_content
                $('#edition_tabs_content').html('<div class="row">' + response.str_tab_div_html + '</div>');
                isEditionChange = 1;
                editionCurrentPage = 18;
            } else {
                $('#edition_tabs_content').html('<div class="row"><div class="col-12 text-center py-5"><i class="bx bx-inbox bx-lg text-muted mb-3"></i><h5 class="text-muted">No archives found</h5></div></div>');
            }
        },
        error: function() {
            $('#edition_tabs_content').html('<div class="col-12 text-center py-5"><i class="bx bx-error bx-lg text-danger mb-3"></i><h5 class="text-danger">Error loading data</h5></div>');
        }
    });
}
</script>

<style>
    /* Archive Grid View */
    .archive-item {
        border: 1px solid #e9ecef;
        border-radius: 16px;
        background: #fff;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        position: relative;
        backdrop-filter: blur(10px);
        margin-bottom: 20px;
    }

    .archive-item:hover {
        box-shadow: 0 12px 32px rgba(0, 123, 255, 0.2);
        transform: translateY(-4px) scale(1.02);
        border-color: #007bff;
        background: linear-gradient(135deg, #fff 0%, #f8f9ff 100%);
    }

    .archive-thumbnail-container {
        position: relative;
        overflow: hidden;
        border-radius: 12px 12px 0 0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
    }

    .archive-thumbnail {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 8px;
    }

    .archive-item:hover .archive-thumbnail {
        transform: scale(1.08);
        filter: brightness(1.1) contrast(1.05);
    }

    /* Archive Info Section */
    .archive-info {
        padding: 8px 12px;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }

    .archive-page {
        font-weight: 600;
        color: #495057;
        font-size: 13px;
        margin-bottom: 2px;
    }

    .archive-category {
        color: #6c757d;
        font-size: 12px;
        font-weight: 500;
    }

    /* Archive Actions */
    .archive-actions {
        padding: 12px 16px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-top: 1px solid #dee2e6;
        display: flex;
        gap: 8px;
        justify-content: center;
        border-radius: 0 0 12px 12px;
    }

    .archive-actions .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dee2e6;
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        color: #6c757d;
        border-radius: 8px;
        font-size: 13px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .archive-actions .btn-icon:hover {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-color: #007bff;
        color: #fff;
        transform: translateY(-2px) scale(1.1);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }

    /* Pagination */
    .pagination-controls .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pagination-info {
        font-weight: 500;
        color: #6c757d;
    }

    /* Form Improvements */
    .form-select,
    .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
    }

    /* Card Improvements */
    .card {
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border-radius: 12px;
    }

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        border: none;
    }

    .card-header .card-title {
        color: white;
        font-weight: 600;
    }

    .card-tools .btn {
        color: white;
        border-color: rgba(255, 255, 255, 0.3);
    }

    .card-tools .btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.5);
    }

    /* Tab Styling */
    #ResultFilter .edition-tab {
        margin-right: 5px;
        margin-bottom: 5px;
        transition: all 0.3s ease;
    }

    #ResultFilter .edition-tab.active {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    #ResultFilter .edition-tab:hover {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    /* Responsive styling */
    @media (max-width: 768px) {
        .archive-item {
            margin-bottom: 20px;
        }

        .pagination-controls {
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }
    }
</style>
@endpush

