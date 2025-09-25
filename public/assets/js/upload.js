/**
 * Upload JavaScript
 * Handles all file upload functionality
 */

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

    // File upload handling
    const uploadZone = document.getElementById('upload-zone');
    const fileInput = document.getElementById('pdf_file');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const submitBtn = document.getElementById('submit-btn');

    // Drag and drop functionality
    if (uploadZone) {
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
    }

    // File input change
    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });
    }

    function handleFile(file) {
        if (file.type !== 'application/pdf') {
            Swal.fire('Error', 'Please select a PDF file.', 'error');
            return;
        }

        if (file.size > 50 * 1024 * 1024) { // 50MB
            Swal.fire('Error', 'File size must be less than 50MB.', 'error');
            return;
        }

        if (fileName) fileName.textContent = file.name;
        if (fileSize) fileSize.textContent = formatFileSize(file.size);
        if (fileInfo) fileInfo.style.display = 'block';
        if (submitBtn) submitBtn.disabled = false;
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
        const file = fileInput ? fileInput.files[0] : null;
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
        if (fileInfo) fileInfo.style.display = 'none';
        $('#upload-progress').hide();
        $('.upload-actions').show();
        if (submitBtn) submitBtn.disabled = false;
    };
});
