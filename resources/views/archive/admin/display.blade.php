@extends('admin.layouts.app')

@section('content')
@include('admin.layouts.partials.page-header', [
'title' => 'Archive Display',
'breadcrumb' => [
'Home' => route('admin.dashboard.index'),
'Archive' => route('admin.archive.categories'),
'Display' => route('admin.archive.display'),
]
])

@include('admin.layouts.partials.alert')

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
                                @foreach($centers as $center)
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
                                @foreach($categories as $category)
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

<!-- Results Summary & Controls -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="results-info">
                        <h6 class="mb-0">
                            <i class="bx bx-archive me-2"></i>
                            <span id="TotalVisibleArchive">Showing 0 - </span>
                            <span id="TotalArchive">0</span> Archives
                        </h6>
                    </div>
                    <!-- <div class="view-controls">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="viewMode" id="gridView" value="grid" checked>
                            <label class="btn btn-outline-primary btn-sm" for="gridView">
                                <i class="bx bx-grid-alt"></i>
                            </label>
                            <input type="radio" class="btn-check" name="viewMode" id="listView" value="list">
                            <label class="btn btn-outline-primary btn-sm" for="listView">
                                <i class="bx bx-list-ul"></i>
                            </label>
                        </div>
                        <div class="btn-group ms-2" role="group">
                            <button class="btn btn-outline-secondary btn-sm" id="refreshResults">
                                <i class="bx bx-refresh"></i>
                            </button>
                        </div>
                    </div> -->
                </div>
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
            <span class="text-muted small">Editions:</span>
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
    @if($archives->count() > 0)
    @foreach($archives as $archive)
    @php
    // Generate thumbnail path based on filepath (same logic as mypdfarchive)
    $thumbnailUrl = null;
    if ($archive->filepath) {
    $path = explode("/", $archive->filepath);
    if (count($path) >= 5) {
    // Use largeThumbName format like mypdfarchive
    if($archive->auto == '0'){
    $largeThumbName = 'epaper-pdfarchive-live-bucket/PDFArchive/thumb-large/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[4]));
    } else {
    $largeThumbName = 'epaper-pdfarchive-live-bucket/PDFArchive/thumb-large/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[5]));
    }
    $thumbnailUrl = 'https://storage.googleapis.com/' . $largeThumbName;
    }
    }
    @endphp
    <!-- <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-4">
        <div class="archive-item">
            <div class="archive-thumbnail-container">
                <img src="{{ $thumbnailUrl ?: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDMwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0yMjUgMTEyLjVIMTg3LjVWNzVIMjI1VjExMi41WiIgZmlsbD0iI0Q5RDlEOSIvPgo8cGF0aCBkPSJNMjI1IDExMi41SDE4Ny41Vjc1IiBzdHJva2U9IiNDQ0NDQ0MiIHN0cm9rZS13aWR0aD0iMyIvPgo8dGV4dCB4PSIxNTAiIHk9IjE5NSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIxIj5GaWxlPC90ZXh0Pgo8dGV4dCB4PSIxNTAiIHk9IjIyNSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE4Ij5Ob3QgYXZhaWxhYmxlPC90ZXh0Pgo8L3N2Zz4K' }}"
                    class="archive-thumbnail"
                    alt="{{ $archive->title ?: 'Archive' }}"
                    onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDMwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0yMjUgMTEyLjVIMTg3LjVWNzVIMjI1VjExMi41WiIgZmlsbD0iI0Q5RDlEOSIvPgo8cGF0aCBkPSJNMjI1IDExMi41SDE4Ny41Vjc1IiBzdHJva2U9IiNDQ0NDQ0MiIHN0cm9rZS13aWR0aD0iMyIvPgo8dGV4dCB4PSIxNTAiIHk9IjE5NSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIxIj5GaWxlPC90ZXh0Pgo8dGV4dCB4PSIxNTAiIHk9IjIyNSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE4Ij5Ob3QgYXZhaWxhYmxlPC90ZXh0Pgo8L3N2Zz4K'">
            </div>
            <div class="archive-info">
                <div class="archive-page">Page {{ $archive->edition_pageno ?? 'N/A' }}</div>
                <div class="archive-category">{{ $archive->category ?? 'N/A' }}</div>
            </div>
            <div class="archive-actions">
                <button class="btn btn-icon" onclick="viewArchive({{ $archive->id }})" title="View Document">
                    <i class="bx bx-file"></i>
                </button>
                <button class="btn btn-icon" onclick="confirmation({{ $archive->id }})" title="Delete">
                    <i class="bx bx-trash"></i>
                </button>
                <button class="btn btn-icon" onclick="editArchive({{ $archive->id }})" title="Edit">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-icon" onclick="downloadArchive({{ $archive->id }})" title="Download">
                    <i class="bx bx-download"></i>
                </button>
            </div>
        </div>
    </div> -->
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
</div>

@endsection

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-loading-modal/2.1.7/jquery.loadingModal.min.js"></script>

<script>
// Global variables for search
var currentPage = 0;
var totalRecords = 0;
var recordsPerPage = 18;
var totalPages = 0;

// Initialize loading modal
// $('body').loadingModal({
//     text: 'Please Wait...',
//     animation: 'spinner',
//     backgroundColor: 'black',
//     opacity: 0.6
// });

$(document).ready(function(){
    // Datepicker initialization
    // $("#startdate").datepicker({ 
    //     autoclose: true, 
    //     todayHighlight: true,
    //     format: 'yyyy/mm/dd'
    // });
    // $("#enddate").datepicker({ 
    //     autoclose: true, 
    //     todayHighlight: true,
    //     format: 'yyyy/mm/dd'
    // });

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
    $('#startdate').val('{{ date("Y/m/d") }}');
    $('#enddate').val('{{ date("Y/m/d") }}');
    
    // Load initial results
    loadArchives(0);
});

// Load archives via AJAX
function loadArchives(page) {
   // showLoading();
    
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
        },
        complete: function() {
           // hideLoading();
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

// Display archives in grid (for data array)
function displayArchives(archives) {
    // Hide tabs and show regular results
    $('#ResultFilter').hide();
    $('#ArchiveSearchRes').show();
    $('.cls-div-pagination').show();
    
    var html = '<div class="row">';
    
    if(archives.length === 0) {
        html += '<div class="col-12 text-center py-5">';
        html += '<i class="bx bx-inbox bx-lg text-muted mb-3"></i>';
        html += '<h5 class="text-muted">No archives found</h5>';
        html += '<p class="text-muted">Try adjusting your search criteria.</p>';
        html += '</div>';
    } else {
        archives.forEach(function(archive) {
            html += createArchiveCard(archive);
        });
    }
    
    html += '</div>';
    $('#ArchiveSearchRes').html(html);
}

// Create individual archive card
function createArchiveCard(archive) {
    var thumbnailUrl = archive.thumbnail_path || 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDMwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0yMjUgMTEyLjVIMTg3LjVWNzVIMjI1VjExMi41WiIgZmlsbD0iI0Q5RDlEOSIvPgo8cGF0aCBkPSJNMjI1IDExMi41SDE4Ny41Vjc1IiBzdHJva2U9IiNDQ0NDQ0MiIHN0cm9rZS13aWR0aD0iMyIvPgo8dGV4dCB4PSIxNTAiIHk9IjE5NSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIxIj5GaWxlPC90ZXh0Pgo8dGV4dCB4PSIxNTAiIHk9IjIyNSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE4Ij5Ob3QgYXZhaWxhYmxlPC90ZXh0Pgo8L3N2Zz4K';
    
    var card = '<div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-4">';
    card += '<div class="archive-item">';
    card += '<div class="archive-thumbnail-container">';
    card += '<img src="' + thumbnailUrl + '" class="archive-thumbnail" alt="' + (archive.title || 'Archive') + '" onerror="this.src=\'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDMwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0yMjUgMTEyLjVIMTg3LjVWNzVIMjI1VjExMi41WiIgZmlsbD0iI0Q5RDlEOSIvPgo8cGF0aCBkPSJNMjI1IDExMi41SDE4Ny41Vjc1IiBzdHJva2U9IiNDQ0NDQ0MiIHN0cm9rZS13aWR0aD0iMyIvPgo8dGV4dCB4PSIxNTAiIHk9IjE5NSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIxIj5GaWxlPC90ZXh0Pgo8dGV4dCB4PSIxNTAiIHk9IjIyNSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE4Ij5Ob3QgYXZhaWxhYmxlPC90ZXh0Pgo8L3N2Zz4K\'">';
    card += '</div>';
    card += '<div class="archive-info">';
    card += '<div class="archive-page">Page ' + (archive.edition_pageno || 'N/A') + '</div>';
    card += '<div class="archive-category">' + (archive.category || 'N/A') + '</div>';
    card += '</div>';
    card += '<div class="archive-actions">';
    card += '<button class="btn btn-icon" onclick="viewArchive(' + archive.id + ')" title="View Document"><i class="bx bx-file"></i></button>';
    card += '<button class="btn btn-icon" onclick="confirmation(' + archive.id + ')" title="Delete"><i class="bx bx-trash"></i></button>';
    card += '<button class="btn btn-icon" onclick="editArchive(' + archive.id + ')" title="Edit"><i class="bx bx-edit"></i></button>';
    card += '<button class="btn btn-icon" onclick="copyArchive(' + archive.id + ')" title="Copy"><i class="bx bx-copy"></i></button>';
    card += '<button class="btn btn-icon" onclick="printArchive(' + archive.id + ')" title="Print"><i class="bx bx-printer"></i></button>';
    card += '</div>';
    card += '</div>';
    card += '</div>';
    
    return card;
}

// Copy archive function (placeholder)
function copyArchive(id) {
    console.log('Copy archive:', id);
    // TODO: Implement copy functionality
    alert('Copy functionality will be implemented later');
}

// Print archive function (placeholder)
function printArchive(id) {
    console.log('Print archive:', id);
    // TODO: Implement print functionality
    alert('Print functionality will be implemented later');
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
    
    // Update page info
    $('#currentPage').text(currentPage + 1);
    $('#totalPages').text(totalPages);
}

// Show loading indicator
// function showLoading() {
//     $('body').loadingModal('show');
// }

// Hide loading indicator
// function hideLoading() {
//     $('body').loadingModal('hide');
// }

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
function viewArchive(id) {
    console.log('View archive:', id);
    alert('View functionality for archive ID: ' + id + ' - To be implemented');
}

function editArchive(id) {
    console.log('Edit archive:', id);
    alert('Edit functionality for archive ID: ' + id + ' - To be implemented');
}

function downloadArchive(id) {
    console.log('Download archive:', id);
    alert('Download functionality for archive ID: ' + id + ' - To be implemented');
}

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
            
            // Simulate delete process (replace with actual delete logic)
            setTimeout(() => {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Archive has been deleted successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
                console.log('Delete archive:', id);
                // TODO: Implement actual delete functionality
            }, 2000);
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
    
    var formData = $('#archiveSearchForm').serializeArray();
    formData.push({name: 'edition_code', value: edition_code});
    formData.push({name: 'pages', value: 0});
    
    editionStartPage = 0;
    $.ajax({
        type: 'POST',
        data: formData,
        url: '{{ route("admin.archive.search") }}',
        beforeSend: function(){
            $("#div_"+edition_code).empty();
            $("#div_"+edition_code).hide();
        },
        success: function(response){
            if(response.success && response.str_tab_div_html) {
                $("#div_"+edition_code).html(response.str_tab_div_html);
                $("#div_"+edition_code).show();
                isEditionChange = 1;
                editionCurrentPage = 18;
            }
        }
    });
}

function fnGetEditionNextPagination(edition_code, total_rec) {
    if(total_rec <= editionCurrentPage){
        alert("No Next Available");
    } else {
        if(isEditionChange == 1){
            editionStartPage = 18;
            isEditionChange = 0;
        } else {
            editionStartPage = parseInt(editionStartPage, 10) + 18;
        }

        var formData = $('#searchForm').serializeArray();
        formData.push({name: 'edition_code', value: edition_code});
        formData.push({name: 'pages', value: editionStartPage});
        
        $.ajax({
            type: 'POST',
            data: formData,
            url: '{{ route("admin.archive.search") }}',
            beforeSend: function(){
                $('ul#edition_tabs li a').each(function(i) {
                    var val = $(this).attr('data-value');
                    if(val == edition_code){
                        $(this).removeAttr("onclick");
                    } else {
                        var str_fn = "fnGetTabData("+val+");"
                        $(this).attr("onclick", str_fn);
                    }
                });
                $("#div_"+edition_code).empty();
                $("#div_"+edition_code).hide();
            },
            success: function(response){
                if(response.success && response.str_tab_div_html) {
                    $("#div_"+edition_code).html(response.str_tab_div_html);
                    $("#div_"+edition_code).show();
                    $("#div_"+edition_code).removeAttr('style');
                    editionCurrentPage = editionCurrentPage + 18;
                }
            }
        });
    }
}

function fnGetEditionPreviousPagination(edition_code, total_rec) {
    if(editionStartPage == 0){
        alert("No Previous Available");
    } else {
        editionStartPage = parseInt(editionStartPage, 10) - 18;
        var formData = $('#searchForm').serializeArray();
        formData.push({name: 'edition_code', value: edition_code});
        formData.push({name: 'pages', value: editionStartPage});
        
        $.ajax({
            type: 'POST',
            data: formData,
            url: '{{ route("admin.archive.search") }}',
            beforeSend: function(){
                $('ul#edition_tabs li a').each(function(i) {
                    var val = $(this).attr('data-value');
                    if(val == edition_code){
                        $(this).removeAttr("onclick");
                    } else {
                        var str_fn = "fnGetTabData("+val+");"
                        $(this).attr("onclick", str_fn);
                    }
                });
                $("#div_"+edition_code).empty();
                $("#div_"+edition_code).hide();
            },
            success: function(response){
                if(response.success && response.str_tab_div_html) {
                    $("#div_"+edition_code).html(response.str_tab_div_html);
                    $("#div_"+edition_code).show();
                    $("#div_"+edition_code).removeAttr('style');
                    editionCurrentPage = editionCurrentPage - 18;
                }
            }
        });
    }
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

    .archive-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .archive-item:hover .archive-overlay {
        opacity: 1;
    }

    .archive-content {
        padding: 16px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        background: linear-gradient(135deg, #fff 0%, #fafbfc 100%);
    }

    .archive-title {
        font-size: 14px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .archive-meta {
        margin-top: auto;
        font-size: 11px;
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

    /* Archive Title Bar */
    .archive-title-bar {
        background: #f8f9fa;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 600;
        color: #495057;
        border-bottom: 1px solid #e9ecef;
        text-align: center;
    }

    /* Archive Category Label */
    .archive-category-label {
        background: #f8f9fa;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 500;
        color: #6c757d;
        text-align: center;
        border-top: 1px solid #e9ecef;
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

    /* Archive List View */
    .list-view {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
    }

    .archive-list-item {
        border-bottom: 1px solid #e9ecef;
        padding: 15px;
        transition: background-color 0.3s ease;
    }

    .archive-list-item:hover {
        background-color: #f8f9fa;
    }

    .archive-list-item:last-child {
        border-bottom: none;
    }

    .archive-list-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
    }

    .archive-list-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }

    .archive-list-meta {
        font-size: 13px;
    }

    .archive-list-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
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

    /* View Controls */
    .view-controls .btn-group {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Results Info */
    .results-info h6 {
        color: #495057;
        font-weight: 600;
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

    /* Enhanced Design Elements */
    .card {
        border: none;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-radius: 16px;
        background: linear-gradient(135deg, #fff 0%, #f8f9ff 100%);
        backdrop-filter: blur(10px);
    }

    /* Grid Spacing */
    .row {
        margin-left: -10px;
        margin-right: -10px;
    }

    .col-lg-2, .col-md-3, .col-sm-4, .col-6 {
        padding-left: 10px;
        padding-right: 10px;
        margin-bottom: 20px;
    }

    /* PDF Box Spacing */
    .pdf_box {
        margin-bottom: 20px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .form-control:focus, .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        transform: translateY(-1px);
    }

    /* SweetAlert Custom Styling */
    .swal2-popup-custom {
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .swal2-title-custom {
        color: #2c3e50;
        font-weight: 700;
        font-size: 24px;
    }

    .swal2-content-custom {
        color: #6c757d;
        font-size: 16px;
    }

    .swal2-confirm {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        border: none !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        padding: 10px 20px !important;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3) !important;
    }

    .swal2-cancel {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
        border: none !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        padding: 10px 20px !important;
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3) !important;
    }

    .swal2-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4) !important;
    }

    .swal2-cancel:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4) !important;
    }

    /* Responsive Improvements */
    @media (max-width: 768px) {
        .archive-item {
            margin-bottom: 20px;
        }

        .archive-list-item {
            padding: 10px;
        }

        .archive-list-actions {
            margin-top: 10px;
        }

        .pagination-controls {
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }
    }

    /* Loading Animation */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255, 255, 255, .3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Badge Improvements */
    .badge {
        font-size: 10px;
        padding: 4px 8px;
        border-radius: 12px;
    }

    /* Button Improvements */
    .btn {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-outline-primary:hover {
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }

    .btn-outline-success:hover {
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }

    .btn-outline-danger:hover {
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }

    /* Tab Styling - Match Quick Filters */
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

    #ResultFilter .tab-content {
        padding: 0;
        background-color: #fff;
        min-height: 300px;
    }

    #ResultFilter .tab-pane {
        display: none;
    }

    #ResultFilter .tab-pane.active {
        display: block;
    }

    #ResultFilter .tab-pane.fade {
        opacity: 0;
        transition: opacity 0.15s linear;
    }

    #ResultFilter .tab-pane.fade.active {
        opacity: 1;
    }

    .cls-div-pagination {
        background-color: #f8f9fa;
        padding: 15px;
        margin: 15px 0;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }

    .cls-div-pagination .btn {
        margin: 0 5px;
        border-radius: 4px;
        font-weight: 500;
    }


    /* Responsive styling */
    @media (max-width: 768px) {
        #ResultFilter .edition-tab {
            margin-bottom: 10px;
        }
        
        .cls-div-pagination {
            text-align: center;
        }
        
        .cls-div-pagination .btn {
            margin: 5px;
            display: inline-block;
        }
    }
</style>
@endpush