<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'center_code',
        'description',
        'region',
        'state',
        'city',
        'status'
    ];

    protected $casts = [
        'status' => 'integer'
    ];

    // Relationships
    public function archives()
    {
        return $this->hasMany(PdfArchive::class, 'center', 'center_code');
    }

    public function categories()
    {
        return $this->hasMany(ArchiveCategory::class, 'center_code', 'center_code');
    }

    public function logins()
    {
        return $this->hasMany(ArchiveLogin::class, 'center', 'center_code');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }
}