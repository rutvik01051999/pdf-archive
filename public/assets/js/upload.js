/**
 * Upload JavaScript
 * Handles all file upload functionality
 */

$(document).ready(function() {
    console.log('Upload script loaded'); // Debug log
    
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
    
    console.log('Elements found:', { uploadZone, fileInput, fileInfo, fileName, fileSize, submitBtn }); // Debug log

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
            console.log('File input changed:', e.target.files); // Debug log
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });
    }
    
    // Fallback: Also handle file input change with jQuery
    $('#pdf_file').on('change', function(e) {
        console.log('jQuery file input changed:', e.target.files); // Debug log
        if (e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    });
    
    // Alternative jQuery approach
    $('#pdf_file').change(function() {
        const file = this.files[0];
        if (file) {
            console.log('Alternative jQuery handler - file selected:', file);
            
            if (file.type === 'application/pdf' && file.size <= 50 * 1024 * 1024) {
                $('#file-name').text(file.name);
                $('#file-size').text(formatFileSize(file.size));
                $('#file-info').show().css({
                    'display': 'block',
                    'visibility': 'visible',
                    'opacity': '1'
                });
                $('#submit-btn').prop('disabled', false);
                console.log('File info displayed via jQuery');
                
                // Store file info in data attributes to prevent loss
                $('#file-info').data('file-name', file.name);
                $('#file-info').data('file-size', formatFileSize(file.size));
            }
        }
    });

    function handleFile(file) {
        console.log('File selected:', file); // Debug log
        
        // Comprehensive client-side validation
        if (!validateFile(file)) {
            return;
        }

        console.log('File info elements:', { fileName, fileSize, fileInfo, submitBtn }); // Debug log
        
        // Multiple approaches to ensure file info is displayed
        if (fileName) {
            fileName.textContent = file.name;
            console.log('File name set to:', file.name);
        }
        
        if (fileSize) {
            fileSize.textContent = formatFileSize(file.size);
            console.log('File size set to:', formatFileSize(file.size));
        }
        
        if (fileInfo) {
            fileInfo.style.display = 'block';
            fileInfo.style.visibility = 'visible';
            fileInfo.style.opacity = '1';
            console.log('File info should be visible now');
        } else {
            console.error('fileInfo element not found!');
        }
        
        if (submitBtn) {
            submitBtn.disabled = false;
            console.log('Submit button enabled');
        }
        
        // Force a re-render
        if (fileInfo) {
            fileInfo.offsetHeight; // Force reflow
        }
        
        // Store file info in data attributes to prevent loss
        if (fileInfo) {
            fileInfo.setAttribute('data-file-name', file.name);
            fileInfo.setAttribute('data-file-size', formatFileSize(file.size));
            fileInfo.setAttribute('data-file-loaded', 'true');
        }
        
        // Also store in localStorage as backup
        try {
            localStorage.setItem('upload_file_name', file.name);
            localStorage.setItem('upload_file_size', formatFileSize(file.size));
        } catch (e) {
            console.log('localStorage not available');
        }
        
        // Start periodic check to ensure file info stays visible
        startFileInfoProtection(file.name, formatFileSize(file.size));
    }
    
    // Function to protect file info from vanishing
    function startFileInfoProtection(fileName, fileSize) {
        // Clear any existing protection interval
        if (window.fileInfoProtectionInterval) {
            clearInterval(window.fileInfoProtectionInterval);
        }
        
        window.fileInfoProtectionInterval = setInterval(function() {
            const fileInfo = document.getElementById('file-info');
            const fileNameEl = document.getElementById('file-name');
            const fileSizeEl = document.getElementById('file-size');
            const fileInput = document.getElementById('pdf_file');
            
            // Check if file info is visible and has content
            if (fileInfo && fileInfo.style.display !== 'block') {
                console.log('File info became hidden, restoring...');
                fileInfo.style.display = 'block';
                fileInfo.style.visibility = 'visible';
                fileInfo.style.opacity = '1';
            }
            
            // Check if file name/size disappeared
            if (fileNameEl && !fileNameEl.textContent) {
                // Try to restore from backup sources
                let backupFileName = fileName || fileInfo.getAttribute('data-file-name') || localStorage.getItem('upload_file_name');
                if (backupFileName) {
                    console.log('File name vanished, restoring from backup...');
                    fileNameEl.textContent = backupFileName;
                }
            }
            
            if (fileSizeEl && !fileSizeEl.textContent) {
                // Try to restore from backup sources
                let backupFileSize = fileSize || fileInfo.getAttribute('data-file-size') || localStorage.getItem('upload_file_size');
                if (backupFileSize) {
                    console.log('File size vanished, restoring from backup...');
                    fileSizeEl.textContent = backupFileSize;
                }
            }
            
            // Check if file input still has a file
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                console.log('No file in input, stopping protection');
                clearInterval(window.fileInfoProtectionInterval);
                window.fileInfoProtectionInterval = null;
                if (fileInfo) fileInfo.style.display = 'none';
            }
        }, 500); // Check every 500ms
        
        // Stop protection after 30 seconds
        setTimeout(() => {
            clearInterval(window.fileInfoProtectionInterval);
            window.fileInfoProtectionInterval = null;
            console.log('File info protection stopped');
        }, 30000);
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
        console.log('Form submission started');
        
        // Validate form data before submission
        if (!validateFormData()) {
            console.log('Form validation failed');
            e.preventDefault();
            return;
        }

        const file = fileInput ? fileInput.files[0] : null;
        console.log('File selected:', file ? file.name : 'No file');
        
        if (!file) {
            console.log('No file selected');
            e.preventDefault();
            showError('Please select a PDF file to upload.');
            return;
        }

        // Final file validation before submission
        if (!validateFile(file)) {
            console.log('File validation failed');
            e.preventDefault();
            return;
        }
        
        console.log('Form validation passed, proceeding with upload');

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
        if (fileInput) fileInput.value = '';
        
        // Clear any protection intervals
        if (window.fileInfoProtectionInterval) {
            clearInterval(window.fileInfoProtectionInterval);
            window.fileInfoProtectionInterval = null;
        }
    };

    // Remove file function
    window.removeFile = function() {
        if (fileInput) fileInput.value = '';
        if (fileInfo) fileInfo.style.display = 'none';
        if (submitBtn) submitBtn.disabled = true;
        
        // Clear protection interval
        if (window.fileInfoProtectionInterval) {
            clearInterval(window.fileInfoProtectionInterval);
            window.fileInfoProtectionInterval = null;
        }
        
        // Show success message
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'File Removed',
                text: 'PDF file has been removed from the upload queue.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    };
    
    // Debug function to test file display
    window.testFileDisplay = function() {
        console.log('Testing file display...');
        const mockFile = {
            name: 'test-file.pdf',
            size: 1024000,
            type: 'application/pdf'
        };
        handleFile(mockFile);
    };
    
    // Make debug function available globally
    console.log('Debug: You can call testFileDisplay() in the console to test file display');
    
    /**
     * Comprehensive file validation function
     */
    function validateFile(file) {
        // Check if file exists
        if (!file) {
            showError('No file selected.');
            return false;
        }

        // Check file type
        const allowedMimeTypes = [
            'application/pdf',
            'application/x-pdf',
            'application/acrobat',
            'application/vnd.pdf',
            'text/pdf',
            'text/x-pdf'
        ];

        if (!allowedMimeTypes.includes(file.type)) {
            showError(`Invalid file type: ${file.type}. Please select a PDF file.`);
            return false;
        }

        // Check file extension
        const fileName = file.name.toLowerCase();
        if (!fileName.endsWith('.pdf')) {
            showError('File must have a .pdf extension.');
            return false;
        }

        // Check file size (50MB max)
        const maxSize = 50 * 1024 * 1024; // 50MB in bytes
        if (file.size > maxSize) {
            const maxSizeMB = maxSize / (1024 * 1024);
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            showError(`File size (${fileSizeMB}MB) exceeds the maximum allowed size of ${maxSizeMB}MB.`);
            return false;
        }

        // Check minimum file size (1KB)
        const minSize = 1024; // 1KB
        if (file.size < minSize) {
            showError('File appears to be empty or too small.');
            return false;
        }

        // Check filename security
        if (!validateFilename(file.name)) {
            return false;
        }

        // Check for suspicious patterns in filename
        if (containsSuspiciousPatterns(file.name)) {
            showError('Filename contains suspicious characters or patterns.');
            return false;
        }

        // All validations passed
        console.log('File validation passed:', file.name);
        return true;
    }

    /**
     * Validate filename security
     */
    function validateFilename(filename) {
        // Check for null bytes
        if (filename.includes('\0')) {
            showError('Filename contains invalid characters.');
            return false;
        }

        // Check for path traversal attempts
        const pathTraversalPatterns = ['../', '..\\', '%2e%2e%2f', '%2e%2e%5c'];
        for (const pattern of pathTraversalPatterns) {
            if (filename.toLowerCase().includes(pattern)) {
                showError('Filename contains invalid path characters.');
                return false;
            }
        }

        // Check filename length
        if (filename.length > 255) {
            showError('Filename is too long (maximum 255 characters).');
            return false;
        }

        // Check for suspicious characters
        const suspiciousChars = ['<', '>', ':', '"', '|', '?', '*', '\\', '/'];
        for (const char of suspiciousChars) {
            if (filename.includes(char)) {
                showError('Filename contains invalid characters.');
                return false;
            }
        }

        // Check for double extensions
        const parts = filename.split('.');
        if (parts.length > 2) {
            const dangerousExtensions = [
                'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8',
                'pl', 'py', 'jsp', 'asp', 'aspx', 'sh', 'cgi', 'exe',
                'bat', 'cmd', 'com', 'scr', 'vbs', 'js', 'jar'
            ];

            for (let i = 0; i < parts.length - 1; i++) {
                if (dangerousExtensions.includes(parts[i].toLowerCase())) {
                    showError('Filename contains suspicious extensions.');
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check for suspicious patterns in filename
     */
    function containsSuspiciousPatterns(filename) {
        const suspiciousPatterns = [
            /\.php\.pdf$/i,
            /\.exe\.pdf$/i,
            /\.bat\.pdf$/i,
            /\.cmd\.pdf$/i,
            /\.scr\.pdf$/i,
            /\.com\.pdf$/i,
            /\.pif\.pdf$/i,
            /\.vbs\.pdf$/i,
            /\.js\.pdf$/i,
            /\.jar\.pdf$/i,
            /\.class\.pdf$/i,
            /\.war\.pdf$/i,
            /\.ear\.pdf$/i
        ];

        for (const pattern of suspiciousPatterns) {
            if (pattern.test(filename)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Show error message
     */
    function showError(message) {
        console.error('File validation error:', message);
        
        // Show error using SweetAlert2 if available, otherwise use alert
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'File Upload Error',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert('File Upload Error: ' + message);
        }

        // Clear file input
        const fileInput = document.getElementById('pdf_file');
        if (fileInput) {
            fileInput.value = '';
        }

        // Hide file info if visible
        const fileInfo = document.getElementById('file-info');
        if (fileInfo) {
            fileInfo.style.display = 'none';
        }

        // Disable submit button
        const submitBtn = document.getElementById('submit-btn');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
    }

    /**
     * Validate form data before submission
     */
    function validateFormData() {
        const title = document.getElementById('title').value.trim();
        const category = document.getElementById('category').value.trim();
        const publishedCenter = document.getElementById('published_center').value.trim();
        const publishedDate = document.getElementById('published_date').value.trim();

        // Validate required fields
        if (!title) {
            showFormError('Event title is required.');
            return false;
        }

        if (!category) {
            showFormError('Category is required.');
            return false;
        }

        if (!publishedCenter) {
            showFormError('Publishing center is required.');
            return false;
        }

        if (!publishedDate) {
            showFormError('Published date is required.');
            return false;
        }

        // Validate field patterns
        if (!/^[a-zA-Z0-9\s\-_.,()]+$/.test(title)) {
            showFormError('Event title contains invalid characters.');
            return false;
        }

        if (!/^[a-zA-Z0-9\s\-_]+$/.test(category)) {
            showFormError('Category contains invalid characters.');
            return false;
        }

        if (!/^[a-zA-Z0-9\s\-_]+$/.test(publishedCenter)) {
            showFormError('Publishing center contains invalid characters.');
            return false;
        }

        // Validate date
        const dateObj = new Date(publishedDate);
        const today = new Date();
        const year1900 = new Date('1900-01-01');

        if (isNaN(dateObj.getTime())) {
            showFormError('Please enter a valid date.');
            return false;
        }

        if (dateObj > today) {
            showFormError('Published date cannot be in the future.');
            return false;
        }

        if (dateObj < year1900) {
            showFormError('Published date must be after 1900.');
            return false;
        }

        return true;
    }

    /**
     * Show form validation error
     */
    function showFormError(message) {
        console.error('Form validation error:', message);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Form Validation Error',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert('Form Validation Error: ' + message);
        }
    }
});
