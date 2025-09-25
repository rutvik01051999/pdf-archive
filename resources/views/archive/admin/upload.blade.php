@extends('admin.layouts.app')

@section('content')
    @include('admin.layouts.partials.page-header', [
        'title' => 'Upload PDF Archive',
        'breadcrumb' => [
            'Home' => route('admin.dashboard.index'),
            'Archive' => route('admin.archive.display'),
            'Upload' => '#',
        ]
    ])

    @include('admin.layouts.partials.alert')

    <!-- File Upload Section -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bx bx-cloud-upload me-2"></i>
                        PDF File Upload
                    </div>
                    <div class="card-tools" style="margin-left: auto;">
                        <a href="{{ route('admin.archive.display') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>
                            Back to Archives
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="upload-area">
                                <div class="upload-zone" id="upload-zone">
                                    <div class="upload-content">
                                        <div class="upload-icon">
                                            <i class="bx bx-cloud-upload fs-48 text-primary mb-3"></i>
                                        </div>
                                        <h4 class="fw-semibold mb-2">Drop PDF file here</h4>
                                        <p class="text-muted mb-4">or click the button below to browse your files</p>
                                        <button type="button" class="btn btn-primary btn-lg" onclick="document.getElementById('pdf_file').click()">
                                            <i class="bx bx-folder-open me-2"></i>
                                            Choose PDF File
                                        </button>
                                        <div class="upload-info mt-3">
                                            <small class="text-muted">
                                                <i class="bx bx-info-circle me-1"></i>
                                                Supported format: PDF only • Max size: 50MB
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="file-info mt-4" id="file-info" style="display: none;">
                                    <div class="alert alert-success">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-file-pdf fs-24 text-danger me-3"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold" id="file-name"></div>
                                                <div class="text-muted small" id="file-size"></div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                                <i class="bx bx-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Details Section -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bx bx-edit me-2"></i>
                        Archive Details
                    </div>
                </div>
                <div class="card-body">
                    <form id="upload-form" action="{{ route('admin.archive.upload.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- File input (must be inside form) -->
                        <input type="file" name="pdf_file" id="pdf_file" accept=".pdf" class="d-none" required>
                        
                        <!-- Basic Information Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="title" class="form-label">
                                    <i class="bx bx-text me-1 text-primary"></i>
                                    Event Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       name="title" id="title" value="{{ old('title') }}" 
                                       placeholder="Enter event title..." required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="category" class="form-label">
                                    <i class="bx bx-category me-1 text-primary"></i>
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('category') is-invalid @enderror" 
                                        name="category" id="category" required>
                                    <option value="">--Select Category--</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->category }}" {{ old('category') == $cat->category ? 'selected' : '' }}>
                                            {{ $cat->category }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Center and Edition Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="published_center" class="form-label">
                                    <i class="bx bx-building me-1 text-primary"></i>
                                    Publishing Center <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('published_center') is-invalid @enderror" 
                                        name="published_center" id="published_center" required>
                                    <option value="">--Select Center--</option>
                                    @if($centers && $centers->count() > 0)
                                        @foreach($centers as $center)
                                            <option value="{{ $center->centercode }}" {{ old('published_center') == $center->centercode ? 'selected' : '' }}>
                                                {{ $center->description }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>No centers available</option>
                                    @endif
                                </select>
                                @error('published_center')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="edition_name" class="form-label">
                                    <i class="bx bx-book me-1 text-primary"></i>
                                    Edition Name
                                </label>
                                <input type="text" class="form-control @error('edition_name') is-invalid @enderror" 
                                       name="edition_name" id="edition_name" value="{{ old('edition_name') }}"
                                       placeholder="Enter edition name...">
                                @error('edition_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Date and Page Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="published_date" class="form-label">
                                    <i class="bx bx-calendar me-1 text-primary"></i>
                                    Published Date <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('published_date') is-invalid @enderror" 
                                       name="published_date" id="published_date" value="{{ old('published_date') }}" 
                                       placeholder="Select date..." required>
                                @error('published_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="edition_pageno" class="form-label">
                                    <i class="bx bx-hash me-1 text-primary"></i>
                                    Page Number
                                </label>
                                <select class="form-select @error('edition_pageno') is-invalid @enderror" 
                                        name="edition_pageno" id="edition_pageno">
                                    @for($i = 1; $i <= 100; $i++)
                                        <option value="{{ $i }}" {{ old('edition_pageno', 1) == $i ? 'selected' : '' }}>
                                            Page {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                @error('edition_pageno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Event Description Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="event" class="form-label">
                                    <i class="bx bx-message-square-detail me-1 text-primary"></i>
                                    Event Description
                                </label>
                                <textarea class="form-control @error('event') is-invalid @enderror" 
                                          name="event" id="event" rows="4" 
                                          placeholder="Describe the event, occasion, or special details about this archive...">{{ old('event') }}</textarea>
                                @error('event')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Optional: Provide additional context about this archive
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="upload-progress" id="upload-progress" style="display: none;">
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-3" style="height: 8px;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                                         role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <small class="text-muted">Uploading to Google Cloud Storage...</small>
                                            </div>
                                        </div>
                                        <div class="upload-actions">
                                            {{-- <button type="button" class="btn btn-outline-secondary me-2" onclick="resetForm()">
                                                <i class="bx bx-reset me-1"></i>
                                                Reset Form
                                            </button> --}}
                                            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                                <i class="bx bx-upload me-2"></i>
                                               Submit
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<!-- Upload CSS -->
<link href="{{ asset('assets/css/upload.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Upload JavaScript -->
<script src="{{ asset('assets/js/upload.js') }}"></script>
<script>
// Additional upload page functionality
$(document).ready(function() {
    // Date picker
    if (typeof flatpickr !== 'undefined') {
        flatpickr("#published_date", {
            dateFormat: "m/d/Y",
            allowInput: true,
            clickOpens: true,
            clearBtn: true
        });
    }
});
</script>
@endpush