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

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bx bx-upload me-2"></i>
                        Upload PDF Archive to Google Cloud Storage
                    </div>
                    <div class="card-tools">
                        <a href="{{ route('admin.archive.display') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>
                            Back to Archives
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <form id="upload-form" action="{{ route('admin.archive.upload.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <!-- Left side - File Upload -->
                            <div class="col-md-6">
                                <div class="upload-area">
                                    <div class="upload-zone" id="upload-zone">
                                        <div class="upload-content">
                                            <i class="bx bx-cloud-upload fs-48 text-primary mb-3"></i>
                                            <h5 class="fw-semibold mb-2">Drop PDF file here</h5>
                                            <p class="text-muted mb-3">or click the button below to browse</p>
                                            <input type="file" name="pdf_file" id="pdf_file" accept=".pdf" class="d-none" required>
                                            <button type="button" class="btn btn-primary" onclick="document.getElementById('pdf_file').click()">
                                                <i class="bx bx-folder-open me-2"></i>
                                                Choose File
                                            </button>
                                        </div>
                                    </div>
                                    <div class="file-info mt-3" id="file-info" style="display: none;">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-file-pdf fs-24 text-danger me-2"></i>
                                            <div>
                                                <div class="fw-semibold" id="file-name"></div>
                                                <div class="text-muted small" id="file-size"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right side - Form Fields -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="title" class="form-label">
                                        <i class="bx bx-text me-1"></i>
                                        Event Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           name="title" id="title" value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="category" class="form-label">
                                        <i class="bx bx-category me-1"></i>
                                        Category <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('category') is-invalid @enderror" 
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
                                
                                <div class="form-group mb-3">
                                    <label for="published_center" class="form-label">
                                        <i class="bx bx-building me-1"></i>
                                        Center <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('published_center') is-invalid @enderror" 
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
                                
                                <div class="form-group mb-3">
                                    <label for="edition_name" class="form-label">
                                        <i class="bx bx-book me-1"></i>
                                        Edition Name
                                    </label>
                                    <input type="text" class="form-control @error('edition_name') is-invalid @enderror" 
                                           name="edition_name" id="edition_name" value="{{ old('edition_name') }}">
                                    @error('edition_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="edition_pageno" class="form-label">
                                        <i class="bx bx-hash me-1"></i>
                                        Page Number
                                    </label>
                                    <select class="form-control @error('edition_pageno') is-invalid @enderror" 
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
                                
                                <div class="form-group mb-3">
                                    <label for="published_date" class="form-label">
                                        <i class="bx bx-calendar me-1"></i>
                                        Published Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('published_date') is-invalid @enderror" 
                                           name="published_date" id="published_date" value="{{ old('published_date') }}" required>
                                    @error('published_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="event" class="form-label">
                                        <i class="bx bx-message-square-detail me-1"></i>
                                        Event Description
                                    </label>
                                    <textarea class="form-control @error('event') is-invalid @enderror" 
                                              name="event" id="event" rows="3" placeholder="Describe the event...">{{ old('event') }}</textarea>
                                    @error('event')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="upload-progress" id="upload-progress" style="display: none;">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                             role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted mt-1">Uploading to Google Cloud Storage...</small>
                                </div>
                                <div class="upload-actions">
                                    <button type="button" class="btn btn-outline-secondary me-2" onclick="resetForm()">
                                        <i class="bx bx-reset me-1"></i>
                                        Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="submit-btn">
                                        <i class="bx bx-upload me-1"></i>
                                        Upload to Cloud
                                    </button>
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
<style>
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.upload-area:hover {
    border-color: #007bff;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.15);
}

.upload-area.dragover {
    border-color: #28a745;
    background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
    transform: scale(1.02);
}

.upload-zone {
    cursor: pointer;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-content {
    text-align: center;
}

.file-info {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
}

.upload-progress {
    flex: 1;
    margin-right: 1rem;
}

.upload-actions {
    display: flex;
    align-items: center;
}

@media (max-width: 768px) {
    .upload-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .upload-progress {
        margin-right: 0;
        margin-bottom: 1rem;
    }
}

/* Ensure dropdown is visible */
#published_center {
    z-index: 1000;
    position: relative;
}

#published_center option {
    padding: 8px 12px;
    background: white;
    color: #333;
}

#published_center:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
@endpush

@push('scripts')
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
$(document).ready(function() {
    // Date picker
    flatpickr("#published_date", {
        dateFormat: "m/d/Y",
        allowInput: true,
        clickOpens: true,
        clearBtn: true
    });

    // File upload handling
    const uploadZone = document.getElementById('upload-zone');
    const fileInput = document.getElementById('pdf_file');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const submitBtn = document.getElementById('submit-btn');

    // Drag and drop functionality
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });

    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });

    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    // Remove click event from upload zone to prevent double triggering
    // Only the "Choose File" button should trigger file selection

    // File input change
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    });

    function handleFile(file) {
        if (file.type !== 'application/pdf') {
            Swal.fire('Error', 'Please select a PDF file.', 'error');
            return;
        }

        if (file.size > 50 * 1024 * 1024) { // 50MB
            Swal.fire('Error', 'File size must be less than 50MB.', 'error');
            return;
        }

        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileInfo.style.display = 'block';
        submitBtn.disabled = false;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Form submission with progress
    $('#upload-form').on('submit', function(e) {
        const file = fileInput.files[0];
        if (!file) {
            e.preventDefault();
            Swal.fire('Error', 'Please select a PDF file to upload.', 'error');
            return;
        }

        // Show progress
        $('#upload-progress').show();
        $('.upload-actions').hide();
        
        // Simulate progress (you can implement real progress tracking)
        let progress = 0;
        const progressBar = $('.progress-bar');
        
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressBar.css('width', progress + '%');
        }, 200);

        // Reset progress after form submission
        setTimeout(() => {
            clearInterval(interval);
            progressBar.css('width', '100%');
        }, 2000);
    });

    // Reset form function
    window.resetForm = function() {
        $('#upload-form')[0].reset();
        fileInfo.style.display = 'none';
        $('#upload-progress').hide();
        $('.upload-actions').show();
        submitBtn.disabled = false;
    };
    
});
</script>
@endpush