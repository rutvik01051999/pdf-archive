<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'center_code',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer'
    ];

    // Relationships
    public function archives()
    {
        return $this->hasMany(PdfArchive::class, 'category', 'name');
    }

    public function center()
    {
        return $this->belongsTo(ArchiveCenter::class, 'center_code', 'center_code');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeByCenter($query, $centerCode)
    {
        return $query->where('center_code', $centerCode);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}