@extends('archive.layouts.app')

@section('title', 'Archive Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="archive-card card text-center">
                <div class="card-body">
                    <i class="fas fa-file-pdf fa-2x text-primary mb-2"></i>
                    <h5 class="card-title">Total Archives</h5>
                    <h3 class="text-primary" id="totalArchives">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="archive-card card text-center">
                <div class="card-body">
                    <i class="fas fa-upload fa-2x text-success mb-2"></i>
                    <h5 class="card-title">Today's Uploads</h5>
                    <h3 class="text-success" id="todayUploads">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="archive-card card text-center">
                <div class="card-body">
                    <i class="fas fa-building fa-2x text-info mb-2"></i>
                    <h5 class="card-title">Active Centers</h5>
                    <h3 class="text-info" id="activeCenters">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="archive-card card text-center">
                <div class="card-body">
                    <i class="fas fa-tags fa-2x text-warning mb-2"></i>
                    <h5 class="card-title">Categories</h5>
                    <h3 class="text-warning" id="totalCategories">-</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="archive-card card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-upload text-primary me-2"></i>
                        Upload New PDF
                    </h5>
                    <p class="card-text">Upload a new PDF document to the archive system.</p>
                    <a href="{{ route('archive.display') }}" class="btn btn-archive">
                        <i class="fas fa-upload me-2"></i>Upload PDF
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="archive-card card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-search text-success me-2"></i>
                        Search Archives
                    </h5>
                    <p class="card-text">Search and browse existing PDF archives.</p>
                    <a href="{{ route('archive.display') }}" class="btn btn-archive">
                        <i class="fas fa-search me-2"></i>Search Archives
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="row">
        <div class="col-12">
            <div class="archive-card card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-upload text-primary me-2"></i>
                        Upload PDF Document
                    </h5>
                </div>
                <div class="card-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pdf_file" class="form-label">PDF File <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept=".pdf" required>
                                    <div class="form-text">Maximum file size: 50MB</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="center" class="form-label">Center <span class="text-danger">*</span></label>
                                    <select class="form-select" id="center" name="center" required>
                                        <option value="">-- Select Center --</option>
                                        @foreach($centers as $center)
                                            <option value="{{ $center->center_code }}">{{ $center->description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->name }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edition_name" class="form-label">Edition Name</label>
                                    <input type="text" class="form-control" id="edition_name" name="edition_name">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="page_number" class="form-label">Page Number</label>
                                    <input type="number" class="form-control" id="page_number" name="page_number" min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_matrix_edition" name="is_matrix_edition" value="1">
                                        <label class="form-check-label" for="is_matrix_edition">
                                            Matrix Edition
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-archive" id="uploadBtn">
                                <span class="spinner-border spinner-border-sm d-none me-2"></span>
                                <i class="fas fa-upload me-2"></i>Upload PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load statistics
    loadStatistics();
    
    // Handle form submission
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Show loading state
        const $btn = $('#uploadBtn');
        const $spinner = $btn.find('.spinner-border');
        const $icon = $btn.find('i');
        
        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $icon.addClass('d-none');
        
        $.ajax({
            url: '{{ route("archive.upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'PDF uploaded successfully!');
                    resetForm();
                    loadStatistics(); // Refresh statistics
                } else {
                    showAlert('danger', response.message || 'Upload failed');
                }
            },
            error: function(xhr) {
                let message = 'Upload failed. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
            },
            complete: function() {
                // Reset button state
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
                $icon.removeClass('d-none');
            }
        });
    });
    
    // Load categories when center changes
    $('#center').on('change', function() {
        const centerCode = $(this).val();
        if (centerCode) {
            loadCategories(centerCode);
        } else {
            $('#category').html('<option value="">-- Select Category --</option>');
        }
    });
    
    function loadStatistics() {
        $.get('{{ route("archive.statistics") }}', function(data) {
            $('#totalArchives').text(data.total_archives || 0);
            $('#todayUploads').text(data.today_uploads || 0);
            $('#activeCenters').text(data.centers_count || 0);
            $('#totalCategories').text(data.categories_count || 0);
        }).fail(function() {
            console.log('Failed to load statistics');
        });
    }
    
    function loadCategories(centerCode) {
        $.get('{{ route("archive.categories") }}', { center: centerCode }, function(data) {
            let options = '<option value="">-- Select Category --</option>';
            data.forEach(function(category) {
                options += `<option value="${category.name}">${category.name}</option>`;
            });
            $('#category').html(options);
        });
    }
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.container-fluid').prepend(alertHtml);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }
    
    function resetForm() {
        $('#uploadForm')[0].reset();
        $('#category').html('<option value="">-- Select Category --</option>');
    }
});

// Make resetForm available globally
function resetForm() {
    $('#uploadForm')[0].reset();
    $('#category').html('<option value="">-- Select Category --</option>');
}
</script>
@endpush

