@extends('admin.layouts.app')

@section('content')
@include('admin.layouts.partials.page-header', [
    'title' => 'Manage Archives',
    'breadcrumb' => [
        'Home' => route('admin.dashboard.index'),
        'Archive' => route('admin.archive.dashboard'),
        'Archives' => route('admin.archive.archives'),
    ],
])

<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="mb-0">Filter Archives</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.archive.archives') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <select class="form-select" name="center">
                                <option value="">All Centers</option>
                                @foreach($centers as $center)
                                    <option value="{{ $center->center_code }}" {{ request('center') == $center->center_code ? 'selected' : '' }}>
                                        {{ $center->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="category">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->name }}" {{ request('category') == $category->name ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('admin.archive.archives') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <h6 class="mb-0">Archives ({{ $archives->total() }} found)</h6>
                </div>
            </div>
            <div class="card-body">
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
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($archives as $archive)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($archive->title, 30) }}</strong>
                                        @if($archive->is_matrix_edition)
                                            <span class="badge bg-primary ms-1">Matrix</span>
                                        @endif
                                    </td>
                                    <td>{{ $archive->center }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $archive->category }}</span>
                                    </td>
                                    <td>{{ Str::limit($archive->edition_name ?: 'N/A', 20) }}</td>
                                    <td>{{ $archive->upload_date->format('d M Y') }}</td>
                                    <td>{{ $archive->file_size_formatted }}</td>
                                    <td>
                                        <span class="badge bg-{{ $archive->status ? 'success' : 'danger' }}">
                                            {{ $archive->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-{{ $archive->status ? 'warning' : 'success' }}" 
                                                    onclick="toggleArchiveStatus({{ $archive->id }}, {{ $archive->status ? 0 : 1 }})">
                                                {{ $archive->status ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteArchive({{ $archive->id }})">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No archives found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $archives->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleArchiveStatus(archiveId, newStatus) {
    $.ajax({
        url: `/admin/archive/archives/${archiveId}/status`,
        type: 'PUT',
        data: { status: newStatus },
        success: function(response) {
            if (response.success) {
                showAlert('success', 'Archive status updated successfully!');
                location.reload();
            } else {
                showAlert('danger', response.message || 'Failed to update archive status');
            }
        },
        error: function(xhr) {
            showAlert('danger', 'Failed to update archive status');
        }
    });
}

function deleteArchive(archiveId) {
    if (confirm('Are you sure you want to delete this archive? This action cannot be undone.')) {
        $.ajax({
            url: `/admin/archive/archives/${archiveId}`,
            type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Archive deleted successfully!');
                    location.reload();
                } else {
                    showAlert('danger', response.message || 'Failed to delete archive');
                }
            },
            error: function(xhr) {
                showAlert('danger', 'Failed to delete archive');
            }
        });
    }
}

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('.container-fluid').prepend(alertHtml);
    
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}
</script>
@endpush

