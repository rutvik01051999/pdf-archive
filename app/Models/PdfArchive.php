<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PdfArchive extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'center',
        'edition_name',
        'edition_code',
        'page_number',
        'pdf_file_path',
        'thumbnail_path',
        'google_cloud_path',
        'google_cloud_thumbnail_path',
        'file_size',
        'file_type',
        'is_matrix_edition',
        'auto_generated',
        'uploaded_by',
        'upload_date',
        'status',
        'remarks'
    ];

    protected $casts = [
        'upload_date' => 'datetime',
        'is_matrix_edition' => 'boolean',
        'auto_generated' => 'boolean',
        'page_number' => 'integer',
        'status' => 'integer'
    ];

    // Relationships
    public function center()
    {
        return $this->belongsTo(ArchiveCenter::class, 'center', 'center_code');
    }

    public function category()
    {
        return $this->belongsTo(ArchiveCategory::class, 'category', 'name');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeByCenter($query, $center)
    {
        return $query->where('center', $center);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeMatrixEdition($query)
    {
        return $query->where('is_matrix_edition', 1);
    }

    // Accessors
    public function getFormattedUploadDateAttribute()
    {
        return $this->upload_date ? $this->upload_date->format('d M Y') : 'N/A';
    }

    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) return 'N/A';
        
        $bytes = (int) $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}