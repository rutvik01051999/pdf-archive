@extends('admin.layouts.app')

@section('content')
@include('admin.layouts.partials.page-header', [
'title' => 'Edit Archive',
'breadcrumb' => [
'Home' => route('admin.dashboard.index'),
'Archive' => route('admin.archive.display'),
'Edit' => '#',
]
])

@include('admin.layouts.partials.alert')

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bx bx-edit me-2"></i>
                    Edit Archive
                </div>
                <div class="card-tools">
                    <a href="{{ route('admin.archive.display') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>
                        Back to Archives
                    </a>
                </div>
            </div>
                
            <div class="card-body">
                <form id="edit-form" action="{{ route('admin.archive.update', $archive->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       id="title" 
                                       name="title" 
                                       value="{{ old('title', $archive->title) }}"
                                       placeholder="Enter archive title">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('category') is-invalid @enderror" 
                                        id="category" 
                                        name="category" 
                                        required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->category }}" 
                                                {{ old('category', $archive->category) == $category->category ? 'selected' : '' }}>
                                            {{ $category->category }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edition_name" class="form-label">Edition Name</label>
                                <input type="text" 
                                       class="form-control @error('edition_name') is-invalid @enderror" 
                                       id="edition_name" 
                                       name="edition_name" 
                                       value="{{ old('edition_name', $archive->edition_name) }}"
                                       placeholder="Enter edition name">
                                @error('edition_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edition_code" class="form-label">Edition Code</label>
                                <input type="number" 
                                       class="form-control @error('edition_code') is-invalid @enderror" 
                                       id="edition_code" 
                                       name="edition_code" 
                                       value="{{ old('edition_code', $archive->edition_code) }}"
                                       placeholder="Enter edition code">
                                @error('edition_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edition_pageno" class="form-label">Page Number</label>
                                <input type="number" 
                                       class="form-control @error('edition_pageno') is-invalid @enderror" 
                                       id="edition_pageno" 
                                       name="edition_pageno" 
                                       value="{{ old('edition_pageno', $archive->edition_pageno) }}"
                                       placeholder="Enter page number">
                                @error('edition_pageno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="published_center" class="form-label">Published Center</label>
                                <select class="form-select @error('published_center') is-invalid @enderror" 
                                        id="published_center" 
                                        name="published_center">
                                    <option value="">Select Center</option>
                                    @foreach($centers as $center)
                                        <option value="{{ $center->centercode }}" 
                                                {{ old('published_center', $archive->published_center) == $center->centercode ? 'selected' : '' }}>
                                            {{ $center->description }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('published_center')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="published_date" class="form-label">Published Date</label>
                                <input type="date" 
                                       class="form-control @error('published_date') is-invalid @enderror" 
                                       id="published_date" 
                                       name="published_date" 
                                       value="{{ old('published_date', $archive->published_date) }}">
                                @error('published_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="filename" class="form-label">Filename</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="filename" 
                                       value="{{ $archive->filename }}" 
                                       readonly>
                                <div class="form-text">Filename cannot be changed</div>
                            </div>
                        </div>
                    </div>
                    
                    @if($archive->filepath)
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Current File</label>
                                <div class="border p-3 rounded bg-light">
                                    <p class="mb-2"><strong>File Path:</strong> {{ $archive->filepath }}</p>
                                    <a href="https://storage.googleapis.com/{{ $archive->filepath }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bx bx-external-link me-1"></i>
                                        View File
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </form>
            </div>
            
            <div class="card-footer">
                <div class="d-flex gap-2">
                    <button type="submit" form="edit-form" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>
                        Update Archive
                    </button>
                    <a href="{{ route('admin.archive.display') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-x me-1"></i>
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#edit-form').on('submit', function(e) {
        var isValid = true;
        
        // Check required fields
        if ($('#category').val() === '') {
            $('#category').addClass('is-invalid');
            isValid = false;
        } else {
            $('#category').removeClass('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
            // Show toast notification instead of alert
            if (typeof toastr !== 'undefined') {
                toastr.error('Please fill in all required fields.');
            } else {
                alert('Please fill in all required fields.');
            }
        }
    });
    
    // Remove validation classes on change
    $('.form-control, .form-select').on('change', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>
@endpush
