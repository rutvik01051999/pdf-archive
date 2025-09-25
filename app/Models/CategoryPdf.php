<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryPdf extends Model
{
    use HasFactory;

    protected $table = 'category_pdf';

    protected $fillable = [
        'category',
        'active_status'
    ];

    protected $casts = [
        'active_status' => 'integer'
    ];

    // Relationships
    public function pdfs()
    {
        return $this->hasMany(Pdf::class, 'category', 'category');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active_status', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('active_status', 2);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('category');
    }

    // Accessors
    public function getStatusAttribute()
    {
        switch ($this->active_status) {
            case 1:
                return 'Active';
            case 2:
                return 'Inactive';
            default:
                return 'Unknown';
        }
    }

    public function getStatusBadgeAttribute()
    {
        switch ($this->active_status) {
            case 1:
                return '<span class="badge bg-success">Active</span>';
            case 2:
                return '<span class="badge bg-danger">Inactive</span>';
            default:
                return '<span class="badge bg-secondary">Unknown</span>';
        }
    }
}
