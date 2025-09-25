@extends('admin.layouts.app')

@section('content')
<!-- Page Header -->
@include('admin.layouts.partials.page-header', [
    'title' => 'Archive Centers',
    'breadcrumb' => [
        'Home' => route('admin.dashboard.index'),
        'Archive' => route('admin.archive.dashboard'),
        'Centers' => route('admin.archive.centers'),
    ],
])

<!-- Add Center Button -->
<div class="row mb-4">
    <div class="col-12">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCenterModal">
            <i class="bx bx-plus me-2"></i>Add New Center
        </button>
    </div>
</div>

<!-- Centers Table -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <h6 class="mb-0">Archive Centers</h6>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Center Code</th>
                                <th>Description</th>
                                <th>Region</th>
                                <th>State</th>
                                <th>City</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($centers as $center)
                                <tr>
                                    <td><strong>{{ $center->center_code }}</strong></td>
                                    <td>{{ $center->description }}</td>
                                    <td>{{ $center->region ?: 'N/A' }}</td>
                                    <td>{{ $center->state ?: 'N/A' }}</td>
                                    <td>{{ $center->city ?: 'N/A' }}</td>
                                    <td>
                                        @if($center->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="editCenter({{ $center->id }})" title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteCenter({{ $center->id }})" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No centers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Center Modal -->
<div class="modal fade" id="addCenterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Center</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCenterForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_center_code" class="form-label">Center Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_center_code" name="center_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_description" name="description" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_region" class="form-label">Region</label>
                        <input type="text" class="form-control" id="add_region" name="region">
                    </div>
                    <div class="mb-3">
                        <label for="add_state" class="form-label">State</label>
                        <input type="text" class="form-control" id="add_state" name="state">
                    </div>
                    <div class="mb-3">
                        <label for="add_city" class="form-label">City</label>
                        <input type="text" class="form-control" id="add_city" name="city">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="add_status" name="status" value="1" checked>
                            <label class="form-check-label" for="add_status">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Center</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Centers JavaScript -->
<script src="{{ asset('assets/js/centers.js') }}"></script>
<script>
// Set up URLs for the centers functionality
window.centersStoreUrl = '{{ route("admin.archive.centers.store") }}';
window.centersDeleteUrl = '/admin/archive/centers/:id';
window.centersEditUrl = '/admin/archive/centers/edit/:id';
</script>
@endpush

