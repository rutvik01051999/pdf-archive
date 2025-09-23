# File Upload Security Implementation

## Overview

This document outlines the comprehensive file upload security measures implemented in the Junior Editor application to prevent malicious file uploads and ensure data integrity.

## Security Features Implemented

### 1. File Type Validation

#### MIME Type Validation
- **Primary Defense**: Validates actual file content using MIME types
- **Supported Types**:
  - Images: `image/jpeg`, `image/png`, `image/gif`, `image/webp`
  - Videos: `video/mp4`, `video/quicktime`, `video/x-msvideo`, `video/x-ms-wmv`, `video/webm`
  - Documents: `text/csv`, `text/plain`, `application/csv`

#### File Extension Validation
- **Secondary Defense**: Validates file extensions against allowed list
- **Prevents**: Double extensions (e.g., `file.jpg.php`)
- **Blocked Extensions**: `php`, `phtml`, `php3`, `php4`, `php5`, `pl`, `py`, `jsp`, `asp`, `sh`, `cgi`, `exe`, `bat`, `cmd`, `com`, `scr`, `vbs`, `js`

### 2. File Size Limits

| File Type | Maximum Size | Configuration |
|-----------|-------------|---------------|
| Images    | 2MB         | `MAX_IMAGE_SIZE` |
| Videos    | 100MB       | `MAX_VIDEO_SIZE` |
| Documents | 2MB         | `MAX_DOCUMENT_SIZE` |

### 3. Content Security Checks

#### Malicious Content Detection
- **PHP Code Detection**: Scans for `<?php` and `<?=` tags
- **Script Tag Detection**: Identifies `<script>` tags
- **Executable Signature Detection**: Detects PE, ELF, and Mach-O executables

#### Image Validation
- **GD Library Validation**: Uses `getimagesize()` and image creation functions
- **Dimension Limits**: Maximum 10,000 x 10,000 pixels
- **Corruption Detection**: Validates image integrity

### 4. Filename Security

#### Secure Filename Generation
- **Random Generation**: Uses timestamp + random string + extension
- **Custom Filenames**: Sanitizes user-provided names using `Str::slug()`
- **Length Limits**: Maximum 50 characters for custom names
- **Character Filtering**: Removes special characters except `-` and `_`

#### Null Byte Protection
- **Detection**: Prevents null byte injection attacks
- **Validation**: Checks filename for `\0` characters

### 5. File Storage Security

#### Secure Storage Paths
- **Organized Structure**: Files stored in type-specific directories
- **Public Disk**: Uses Laravel's public disk with proper permissions
- **Path Validation**: Prevents directory traversal attacks

#### File Deletion
- **Secure Deletion**: Uses FileUploadService for consistent deletion
- **Logging**: Logs all file operations for audit trails

## Implementation Details

### FileUploadService

The core service handles all file upload operations with comprehensive security checks:

```php
// Usage Example
$fileUploadService = new FileUploadService();
$uploadResult = $fileUploadService->uploadFile(
    $request->file('image'),
    'image',
    'banners',
    'custom-filename' // optional
);
```

### SecureFileUpload Validation Rule

Custom validation rule for form validation:

```php
// Usage in validation rules
'image' => ['required', new SecureFileUpload('image')],
'video' => ['nullable', new SecureFileUpload('video')],
'csv_file' => ['required', new SecureFileUpload('document')],
```

### Configuration

File upload settings in `config/upload.php`:

```php
'allowed_types' => [
    'image' => [
        'mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'max_size' => 2048, // 2MB in KB
    ],
    // ... other types
],
```

## Updated Controllers

The following controllers have been updated to use the secure file upload system:

1. **VideoController** - Video file uploads
2. **BannerSectionController** - Image uploads for banners
3. **SliderController** - Image uploads for sliders
4. **WinnerController** - CSV file uploads

## Security Benefits

### 1. Prevention of Malicious Uploads
- **Code Injection**: Prevents PHP/script code in uploaded files
- **Executable Uploads**: Blocks executable files
- **Double Extensions**: Prevents bypassing with double extensions

### 2. Data Integrity
- **File Validation**: Ensures files are not corrupted
- **Type Verification**: Confirms file type matches extension
- **Size Limits**: Prevents resource exhaustion

### 3. Audit Trail
- **Comprehensive Logging**: All file operations are logged
- **User Tracking**: Links uploads to authenticated users
- **Error Logging**: Detailed error information for debugging

### 4. Performance Protection
- **Size Limits**: Prevents large file uploads that could impact performance
- **Dimension Limits**: Prevents oversized images
- **Efficient Validation**: Optimized validation process

## Environment Variables

Configure the following environment variables for customization:

```env
# File size limits (in KB)
MAX_IMAGE_SIZE=2048
MAX_VIDEO_SIZE=102400
MAX_DOCUMENT_SIZE=2048

# Security settings
SECURE_FILENAMES=true
SCAN_FOR_VIRUSES=false
```

## Best Practices

### 1. Regular Updates
- Keep the allowed file types list updated
- Monitor for new security threats
- Update blocked extensions as needed

### 2. Monitoring
- Review upload logs regularly
- Monitor for failed upload attempts
- Check for suspicious patterns

### 3. Testing
- Test with various file types
- Verify size limit enforcement
- Test malicious file detection

## Future Enhancements

### 1. Virus Scanning
- Integration with ClamAV for virus detection
- Real-time malware scanning
- Quarantine system for suspicious files

### 2. Advanced Content Analysis
- Machine learning-based content detection
- Advanced image analysis
- Document content validation

### 3. Cloud Storage Integration
- Secure cloud storage options
- CDN integration for performance
- Backup and redundancy systems

## Troubleshooting

### Common Issues

1. **File Upload Fails**
   - Check file size limits
   - Verify MIME type is allowed
   - Ensure file extension is permitted

2. **Image Validation Errors**
   - Verify image is not corrupted
   - Check image dimensions
   - Ensure GD extension is installed

3. **Permission Errors**
   - Check storage directory permissions
   - Verify Laravel storage configuration
   - Ensure proper disk setup

### Debug Mode

Enable detailed logging by setting log level to debug in `config/logging.php`:

```php
'level' => env('LOG_LEVEL', 'debug'),
```

## Conclusion

The implemented file upload security system provides comprehensive protection against malicious uploads while maintaining usability and performance. Regular monitoring and updates ensure continued security effectiveness.
