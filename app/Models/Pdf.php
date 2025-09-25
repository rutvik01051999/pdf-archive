<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pdf extends Model
{
    use HasFactory;

    protected $table = 'pdf';

    protected $fillable = [
        'filename',
        'filepath',
        'download_url',
        'title',
        'category',
        'edition_name',
        'edition_code',
        'edition_pageno',
        'published_center',
        'published_date',
        'event',
        'upload_date',
        'username',
        'auto',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'upload_date' => 'datetime',
        'published_date' => 'date',
        'edition_code' => 'integer',
        'edition_pageno' => 'integer',
        'auto' => 'boolean'
    ];

    // Relationships
    public function categoryRelation()
    {
        return $this->belongsTo(CategoryPdf::class, 'category', 'category');
    }

    public function center()
    {
        return $this->belongsTo(ArchiveCenter::class, 'published_center', 'center_code');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeByCenter($query, $center)
    {
        return $query->where('published_center', $center);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByEdition($query, $editionCode)
    {
        return $query->where('edition_code', $editionCode);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('published_date', [$startDate, $endDate]);
    }

    public function scopeMatrixEdition($query)
    {
        return $query->where('auto', 1);
    }

    public function scopeManualUpload($query)
    {
        return $query->where('auto', 0);
    }

    // Accessors
    public function getFormattedUploadDateAttribute()
    {
        return $this->upload_date ? $this->upload_date->format('d M Y') : 'N/A';
    }

    public function getFormattedPublishedDateAttribute()
    {
        return $this->published_date ? $this->published_date->format('d M Y') : 'N/A';
    }

    public function getThumbnailPathAttribute()
    {
        if (!$this->filepath) return null;
        
        $path = explode("/", $this->filepath);
        if (count($path) >= 5) {
            if ($this->auto == '1') {
                $thumbnailName = 'epaper-pdfarchive-live-bucket/PDFArchive/thumb-large/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[5] ?? $path[4])));
            } else {
                $thumbnailName = 'epaper-pdfarchive-live-bucket/PDFArchive/thumb-large/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", $path[4])));
            }
            return 'https://storage.googleapis.com/' . $thumbnailName;
        }
        
        return null;
    }

    public function getPdfUrlAttribute()
    {
        if (!$this->filepath) return null;
        
        // Convert storage path to Google Cloud URL
        $pdfUrl = $this->filepath;
        if (strpos($pdfUrl, 'epaper-archive-storage') !== false) {
            $pdfUrl = str_replace('epaper-archive-storage', 'epaper-pdfarchive-live-bucket', $pdfUrl);
        }
        
        return 'https://storage.googleapis.com/' . $pdfUrl;
    }

    public function getIsNewAttribute()
    {
        if (!$this->filename) return false;
        
        $filename = strtolower($this->filename);
        return (strpos($filename, 'alter') !== false || strpos($filename, 'new') !== false);
    }
}
