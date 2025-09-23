@extends('archive.layouts.app')

@section('title', 'Archive Display')

@section('content')
<div class="container-fluid">
    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="archive-card card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-search text-primary me-2"></i>
                        Search & Filter Archives
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('archive.display') }}" id="searchForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="center" class="form-label">Center</label>
                                    <select class="form-select" id="center" name="center">
                                        <option value="">All Centers</option>
                                        @foreach($centers as $center)
                                            <option value="{{ $center->center_code }}" 
                                                    {{ request('center') == $center->center_code ? 'selected' : '' }}>
                                                {{ $center->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->name }}" 
                                                    {{ request('category') == $category->name ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ request('end_date') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Search by Title</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="{{ request('title') }}" placeholder="Enter title to search">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edition_name" class="form-label">Edition Name</label>
                                    <input type="text" class="form-control" id="edition_name" name="edition_name" 
                                           value="{{ request('edition_name') }}" placeholder="Enter edition name">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-archive">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                            <a href="{{ route('archive.display') }}" class="btn btn-secondary">
                                <i class="fas fa-undo me-2"></i>Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="row">
        <div class="col-12">
            <div class="archive-card card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary me-2"></i>
                        Archive Results ({{ $archives->total() }} found)
                    </h5>
                    <div>
                        <a href="{{ route('archive.index') }}" class="btn btn-archive btn-sm">
                            <i class="fas fa-upload me-2"></i>Upload New
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($archives->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Center</th>
                                        <th>Category</th>
                                        <th>Edition</th>
                                        <th>Upload Date</th>
                                        <th>File Size</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($archives as $archive)
                                        <tr>
                                            <td>
                                                <strong>{{ $archive->title }}</strong>
                                                @if($archive->is_matrix_edition)
                                                    <span class="badge bg-primary ms-2">Matrix</span>
                                                @endif
                                            </td>
                                            <td>{{ $archive->center }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $archive->category }}</span>
                                            </td>
                                            <td>{{ $archive->edition_name ?: 'N/A' }}</td>
                                            <td>{{ $archive->formatted_upload_date }}</td>
                                            <td>{{ $archive->file_size_formatted }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('archive.view', $archive->id) }}" 
                                                       class="btn btn-outline-primary" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('archive.download', $archive->id) }}" 
                                                       class="btn btn-outline-success" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteArchive({{ $archive->id }})" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $archives->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No archives found</h5>
                            <p class="text-muted">Try adjusting your search criteria or upload a new PDF.</p>
                            <a href="{{ route('archive.index') }}" class="btn btn-archive">
                                <i class="fas fa-upload me-2"></i>Upload PDF
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this archive? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let deleteArchiveId = null;

function deleteArchive(id) {
    deleteArchiveId = id;
    $('#deleteModal').modal('show');
}

$('#confirmDelete').on('click', function() {
    if (deleteArchiveId) {
        $.ajax({
            url: `/archive/delete/${deleteArchiveId}`,
            type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Archive deleted successfully');
                    $('#deleteModal').modal('hide');
                    // Reload the page to refresh the list
                    location.reload();
                } else {
                    showAlert('danger', response.message || 'Delete failed');
                }
            },
            error: function(xhr) {
                let message = 'Delete failed. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
            },
            complete: function() {
                $('#deleteModal').modal('hide');
            }
        });
    }
});

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

// Auto-submit form on filter change
$('#center, #category').on('change', function() {
    $('#searchForm').submit();
});
</script>
@endpush

