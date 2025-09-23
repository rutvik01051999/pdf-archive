@extends('admin.layouts.app')

@section('content')
    @include('admin.layouts.partials.page-header', [
        'title' => 'Archive Upload',
        'breadcrumb' => [
            'Home' => route('admin.dashboard.index'),
            'Archive' => route('admin.archive.categories'),
            'Upload' => route('admin.archive.upload'),
        ]
    ])

    @include('admin.layouts.partials.alert')

    <!-- Archive Upload Form -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bx bx-upload me-2"></i>
                        PDF Archive Upload
                    </div>
                </div>
                <div class="card-body">
                    <!-- Success/Error Messages -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <span id="success-span" style="display:none; color: green; font-weight: bold;"></span>
                            <span id="error-span" style="display:none; color: red; font-weight: bold;"></span>
                        </div>
                    </div>

                    <form id="archiveUploadForm" enctype="multipart/form-data" autocomplete="off">
                        @csrf
                        <div class="row">
                            <!-- File Upload Section -->
                            <div class="col-xl-6">
                                <div class="card custom-card">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <i class="bx bx-file me-2"></i>
                                            File Upload
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="files" class="form-label">
                                                <strong>Select PDF Files:</strong>
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="file" id="files" name="files[]" class="form-control" multiple accept="application/pdf" required>
                                            <div class="form-text text-danger">Maximum 20 files allowed</div>
                                        </div>
                                        
                                        <!-- Selected Files Display -->
                                        <div class="selected-files-grid">
                                            <div class="row" id="selectedFiles">
                                                <!-- Selected files will be displayed here as cards -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Details Section -->
                            <div class="col-xl-6">
                                <div class="card custom-card">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <i class="bx bx-detail me-2"></i>
                                            Archive Details
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Matrix Edition Checkbox -->
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_matrix_edition" name="is_matrix_edition" value="1">
                                                <label class="form-check-label" for="is_matrix_edition">
                                                    <strong>Same As Matrix Edition</strong>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Center Dropdown -->
                                        <div class="mb-3">
                                            <label for="center" class="form-label">
                                                <strong>Center:</strong>
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control cls-valid" name="center" id="center" required>
                                                <option value="">--Select Center--</option>
                                                <option value="001">Mumbai</option>
                                                <option value="002">Delhi</option>
                                                <option value="003">Bangalore</option>
                                                <option value="004">Chennai</option>
                                                <option value="005">Kolkata</option>
                                            </select>
                                        </div>

                                        <!-- Category Dropdown -->
                                        <div class="mb-3">
                                            <label for="category" class="form-label">
                                                <strong>Category:</strong>
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control cls-valid" name="category" id="category" required>
                                                <option value="">--Select Category--</option>
                                                <option value="Matrix Auto">Matrix Auto</option>
                                                <option value="News">News</option>
                                                <option value="Sports">Sports</option>
                                                <option value="Business">Business</option>
                                                <option value="Entertainment">Entertainment</option>
                                            </select>
                                        </div>

                                        <!-- Edition Name -->
                                        <div class="mb-3">
                                            <label for="ename" class="form-label">
                                                <strong>Edition Name:</strong>
                                            </label>
                                            <input type="text" class="form-control d-none" placeholder="Enter Edition Name" name="txt_ename" id="txt_ename">
                                            <select class="form-control" name="ename" id="ename">
                                                <option value="">--Select Edition--</option>
                                            </select>
                                        </div>

                                        <!-- Edition Page No -->
                                        <div class="mb-3">
                                            <label for="pno" class="form-label">
                                                <strong>Edition Page No:</strong>
                                            </label>
                                            <select class="form-control cls-select" id="pno" name="pno">
                                                <option value="">--Select Page No--</option>
                                                @for($i = 1; $i <= 100; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>

                                        <!-- Event -->
                                        <div class="mb-3">
                                            <label for="title" class="form-label">
                                                <strong>Event:</strong>
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input class="form-control cls-valid" placeholder="Enter Event Name" required type="text" name="title" id="title">
                                        </div>

                                        <!-- Event Description -->
                                        <div class="mb-3">
                                            <label for="event" class="form-label">
                                                <strong>Event Description:</strong>
                                            </label>
                                            <textarea class="form-control" placeholder="Enter Event Description" name="event" rows="3" id="event"></textarea>
                                        </div>

                                        <!-- Published Date -->
                                        <div class="mb-3">
                                            <label for="pdate" class="form-label">
                                                <strong>Published Date:</strong>
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input class="form-control cls-valid" placeholder="Enter Published Date" required type="date" name="pdate" id="pdate" value="{{ date('Y-m-d') }}">
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="mb-3">
                                            <button type="button" id="btnSubmit" name="Submit" class="btn btn-primary">
                                                <i class="bx bx-upload me-1"></i> Upload Files
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

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Set default category to "Matrix Auto"
    $('#category').val('Matrix Auto');

    // File selection handler
    document.addEventListener("DOMContentLoaded", init, false);
    
    function init() {
        document.querySelector('#files').addEventListener('change', handleFileSelect, false);
    }
        
    function handleFileSelect(e) {
        $('#success-span').hide();
        $('#error-span').hide();
        if(!e.target.files) return;
        
        var selectedFilesContainer = document.querySelector("#selectedFiles");
        selectedFilesContainer.innerHTML = "";
        
        var files = e.target.files;
        for(var i = 0; i < files.length; i++) {
            var f = files[i];
            var cnt = i + 1;
            
            // Create file card similar to archive display layout
            var fileCard = `
                <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-3">
                    <div class="archive-item">
                        <div class="archive-thumbnail-container">
                            <div class="archive-thumbnail-preview">
                                <i class="bx bx-file-pdf bx-lg text-danger"></i>
                                <div class="file-info">
                                    <small class="text-muted">${f.name}</small>
                                    <small class="text-muted d-block">${(f.size / 1024 / 1024).toFixed(2)} MB</small>
                                </div>
                            </div>
                        </div>
                        <div class="archive-info">
                            <div class="archive-page">File ${cnt}</div>
                            <div class="archive-category">PDF Document</div>
                        </div>
                        <div class="archive-actions">
                            <button class="btn btn-icon" onclick="removeFile(${i})" title="Remove File">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            selectedFilesContainer.innerHTML += fileCard;
        }
    }

    // Function to remove a file from selection
    function removeFile(index) {
        var fileInput = document.getElementById('files');
        var files = Array.from(fileInput.files);
        files.splice(index, 1);
        
        // Create a new FileList (simulated)
        var dt = new DataTransfer();
        files.forEach(function(file) {
            dt.items.add(file);
        });
        fileInput.files = dt.files;
        
        // Refresh the display
        handleFileSelect({target: fileInput});
    }

    // Matrix Edition checkbox change handler
    $('#is_matrix_edition').change(function() {
        if (this.checked) {
            $("#ename").val('');
            $("#pno").val('');
            $("#title").val('');
            $("#event").val('');
            $("#ename").prop('disabled', true);
            $("#pno").prop('disabled', true);
            $("#title").prop('disabled', true);
            $("#event").prop('disabled', true);
        } else {
            $("#ename").prop('disabled', false);
            $("#pno").prop('disabled', false);
            $("#title").prop('disabled', false);
            $("#event").prop('disabled', false);
        }
    });

    // Center change handler - Load sample editions
    $("#center").change(function(){
        var center_id = $(this).val();
        if (center_id) {
            // Load sample editions for demo
            var sampleEditions = [
                {code: 'ED001', name: 'Morning Edition'},
                {code: 'ED002', name: 'Evening Edition'},
                {code: 'ED003', name: 'Special Edition'},
                {code: 'ED004', name: 'Weekend Edition'}
            ];
            
            $('#ename').empty();
            $('#ename').append('<option value="">--Select Edition--</option>');
            for (var i = 0; i < sampleEditions.length; i++) {
                $('#ename').append('<option value="'+sampleEditions[i]['code']+'~'+sampleEditions[i]['name']+'">'+sampleEditions[i]['name']+'</option>');
            }
        }
    });

    // Category change handler
    $('#category').change(function(){
        var val = $(this).val();
        if (val != 'Matrix Auto') {
            $("#ename").val('');
            $("#txt_ename").val('');
            $("#pno").val('');
            $("#title").val('');
            $("#event").val('');
            $("#pno").prop('disabled', true);
            $('#txt_ename').removeClass('d-none');
            $('#ename').addClass('d-none');
        } else {
            $("#ename").val('');
            $("#txt_ename").val('');
            $("#pno").val('');
            $("#title").val('');
            $("#event").val('');
            $("#pno").prop('disabled', false);
            $('#ename').removeClass('d-none');
            $('#txt_ename').addClass('d-none');
        }
    });

    // Edition name change handler
    $('#ename').on('change', function (e) {
        var str_val = '';
        var val_arr = this.value.split("~");
        str_val = val_arr[1];
        var str_pno = $("#pno").val();
        str_val += ' Page ' + str_pno;
        $("#title").val(str_val);
    });
    
    // Page number change handler
    $("#pno").on('change', function (e) {
        var val = $('#category').val();
        if (val == 'Matrix Auto') {
            var str_ename = $("#ename").val();
            var pno = this.value;
            if(str_ename != ''){
                var val_arr = str_ename.split("~");
                var str_val = val_arr[1]+' Page '+pno;
                $("#title").val(str_val);
            } else {
                var str_val = 'Page '+pno;
                $("#title").val(str_val);
            }
        }
    });

    // Form submission handler
    $("#btnSubmit").click(function(e){
        $('#success-span').hide();
        $('#error-span').hide();

        var isValid = true;
        $('.cls-valid').each(function() {
            if ($.trim($(this).val()) == '') {
                isValid = false;
                $(this).css({
                    "border": "1px solid red"
                });
            }
            else {
                $(this).css({
                    "border": ""
                });
            }
        });
        
        if(isValid == false){
            $('#error-span').html('Please fill required fields.');
            $('#error-span').show();
            e.preventDefault();
        } else {
            var file_length = $('#files').get(0).files.length;
            if (file_length > 20) {
                $('#error-span').html('You can upload maximum 20 files.');
                $('#error-span').show();
                e.preventDefault();
            } else if (file_length == 0) {
                $('#error-span').html('Please select at least one file.');
                $('#error-span').show();
                e.preventDefault();
            } else {
                $('#success-span').hide();
                $('#error-span').hide();
                
                var form_data = new FormData();
                var is_matrix_edition = 0;
                if($("#is_matrix_edition").is(":checked")){
                    is_matrix_edition = $("#is_matrix_edition").val();
                }
                
                // Add form data
                form_data.append('is_matrix_edition', is_matrix_edition);
                form_data.append('center', $("#center").val());
                form_data.append('category', $("#category").val());
                form_data.append('ename', $("#ename").val());
                form_data.append('txt_ename', $("#txt_ename").val());
                form_data.append('pno', $("#pno").val());
                form_data.append('title', $("#title").val());
                form_data.append('event', $("#event").val());
                form_data.append('pdate', $("#pdate").val());
                form_data.append('_token', $('meta[name="csrf-token"]').attr('content'));

                // Add files
                $.each($("#files"), function(i, obj) {
                    $.each(obj.files, function(i, file){
                        form_data.append('files['+i+']', file);
                    });
                });

                // Demo form submission - no actual upload
                $("#btnSubmit").prop('disabled', true);
                Swal.fire({
                    title: 'Demo Mode',
                    text: 'This is a demo view. No files will be actually uploaded.',
                    icon: 'info',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    // Reset form
                    $("#files").val('');
                    $("#selectedFiles").empty();
                    $("#archiveUploadForm")[0].reset();
                    $('#category').val('Matrix Auto');
                    $("#btnSubmit").prop('disabled', false);
                });
            }
        }
    });
});
</script>

<style>
/* Selected Files Grid Layout - Same as Archive Display */
.selected-files-grid {
    min-height: 100px;
}

.archive-item {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    background: #fff;
    transition: all 0.3s ease;
    overflow: hidden;
}

.archive-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.archive-thumbnail-container {
    position: relative;
    height: 120px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.archive-thumbnail-preview {
    text-align: center;
    padding: 10px;
}

.archive-thumbnail-preview i {
    margin-bottom: 8px;
}

.file-info {
    margin-top: 8px;
}

.file-info small {
    display: block;
    font-size: 10px;
    word-break: break-all;
    line-height: 1.2;
}

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

.archive-actions {
    padding: 8px 12px;
    background: #fff;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 4px;
    justify-content: center;
}

.archive-actions .btn-icon {
    width: 28px;
    height: 28px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #dee2e6;
    background: #fff;
    color: #6c757d;
    border-radius: 4px;
    font-size: 12px;
    transition: all 0.2s ease;
}

.archive-actions .btn-icon:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
    color: #495057;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .archive-item {
        margin-bottom: 15px;
    }
    
    .archive-thumbnail-container {
        height: 100px;
    }
    
    .archive-thumbnail-preview i {
        font-size: 2rem !important;
    }
}
</style>
@endpush
