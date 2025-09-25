<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatrixReportCenter extends Model
{
    protected $connection = 'centers';
    protected $table = 'matrix_report_centers';

    protected $fillable = [
        'centercode',
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
    public function pdfs()
    {
        return $this->hasMany(Pdf::class, 'published_center', 'centercode');
    }

    public function editions()
    {
        return $this->hasMany(Edition::class, 'centercode', 'centercode');
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

    public function scopeOrdered($query)
    {
        return $query->orderBy('description');
    }

    // Accessors
    public function getFormattedDescriptionAttribute()
    {
        return $this->centercode . ' - ' . $this->description;
    }
}
