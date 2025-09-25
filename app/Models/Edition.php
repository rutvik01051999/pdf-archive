<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Edition extends Model
{
    protected $connection = 'matrix';
    protected $table = 'editions';

    protected $fillable = [
        'editioncode',
        'description',
        'centercode',
        'active_status'
    ];

    protected $casts = [
        'active_status' => 'integer'
    ];

    // Relationships
    public function center()
    {
        return $this->belongsTo(MatrixReportCenter::class, 'centercode', 'centercode');
    }

    public function pdfs()
    {
        return $this->hasMany(Pdf::class, 'edition_code', 'editioncode');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active_status', 1);
    }

    public function scopeByCenter($query, $centerCode)
    {
        return $query->where('centercode', $centerCode);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('description');
    }

    // Accessors
    public function getFormattedDescriptionAttribute()
    {
        return $this->editioncode . ' - ' . $this->description;
    }
}
