# PDF Archive System Setup Guide

## Overview
The PDF Archive system has been successfully integrated into your Laravel project. This system allows users to upload, manage, and view PDF documents with Google Cloud Storage integration.

## Features Implemented

### ✅ **Core Functionality**
- PDF upload with automatic thumbnail generation
- Google Cloud Storage integration
- External API authentication (Hono)
- Multi-center support
- Category management
- Search and filtering
- Admin dashboard and management

### ✅ **User Interface**
- Archive user login system
- PDF upload interface
- Archive browsing and search
- User profile management
- Admin management panel

### ✅ **Admin Features**
- Archive dashboard with statistics
- Center management
- Category management
- User management
- Login logs tracking
- Archive management

## Database Structure

The following tables have been created:
- `pdf_archives` - Main archive storage
- `archive_categories` - Category definitions
- `archive_centers` - Center definitions
- `archive_logins` - User accounts
- `archive_login_logs` - Login tracking

## Configuration Required

### 1. Google Cloud Storage Setup
Add these environment variables to your `.env` file:

```env
# Google Cloud Storage Configuration
GOOGLE_CLOUD_PROJECT_ID=your-project-id
GOOGLE_CLOUD_KEY_FILE_PATH=/path/to/your/service-account-key.json
GOOGLE_CLOUD_BUCKET_NAME=matrix-archive-bucket
```

### 2. External API Configuration
```env
# External API Configuration (for Hono authentication)
HONO_API_URL=https://rest.dbclmatrix.com/auth
HONO_API_TOKEN=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJEQkNMIiwibmFtZSI6IkF1dGggQXBpIiwiaWF0IjoxfQ.BuZj--wJRr-oHcsJF8KpSA9OUld7DM5xe3RfB6ZbAu0
```

## Access Points

### Archive User Interface
- **Login**: `/archive/login`
- **Dashboard**: `/archive/` (after login)
- **View Archives**: `/archive/display`
- **Profile**: `/archive/profile`

### Admin Interface
- **Archive Dashboard**: `/admin/archive/dashboard`
- **Manage Archives**: `/admin/archive/archives`
- **Categories**: `/admin/archive/categories`
- **Centers**: `/admin/archive/centers`
- **Users**: `/admin/archive/users`
- **Login Logs**: `/admin/archive/login-logs`

## Sample Data

The system has been populated with sample data:
- **8 Centers**: Mumbai, Delhi, Bangalore, Chennai, Kolkata, Hyderabad, Pune, Ahmedabad
- **8 Categories**: Newspapers, Magazines, Books, Reports, Brochures, Certificates, Forms, Manuals

## Key Features

### 🔐 **Dual Authentication System**
- **Archive Users**: External API authentication via Hono
- **Admin Users**: Existing admin authentication system

### 📁 **File Management**
- Automatic thumbnail generation using Imagick
- Google Cloud Storage for file storage
- Signed URLs for secure file access
- File size and type validation

### 🏢 **Multi-Center Support**
- Different centers can manage their own PDFs
- Center-specific categories
- User management per center

### 📊 **Admin Dashboard**
- Real-time statistics
- Recent activity tracking
- User and archive management
- Login monitoring

## Next Steps

1. **Configure Google Cloud Storage**:
   - Create a Google Cloud project
   - Enable Cloud Storage API
   - Create a service account
   - Download the JSON key file
   - Update environment variables

2. **Test the System**:
   - Access `/archive/login` to test user login
   - Access `/admin/archive/dashboard` to test admin features
   - Upload a test PDF to verify functionality

3. **Customize as Needed**:
   - Modify categories and centers
   - Adjust UI styling
   - Add additional features

## Troubleshooting

### Common Issues:
1. **Google Cloud Storage errors**: Check project ID, key file path, and bucket name
2. **Authentication failures**: Verify external API configuration
3. **File upload issues**: Check file size limits and Imagick installation
4. **View not found errors**: Ensure all view files are in place

### Dependencies Required:
- PHP Imagick extension for PDF thumbnail generation
- Google Cloud Storage PHP client library
- Laravel 12 framework

## Support

The PDF Archive system is now fully integrated and ready to use. All core functionality from the original mypdfarchive project has been converted to Laravel with enhanced features and better security.

