<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ArchiveLogin extends Model
{
    use HasFactory;

    protected $fillable = [
        'uname',
        'full_name',
        'center',
        'email',
        'phone',
        'status',
        'last_login'
    ];

    protected $casts = [
        'status' => 'integer',
        'last_login' => 'datetime'
    ];

    // Relationships
    public function center()
    {
        return $this->belongsTo(ArchiveCenter::class, 'center', 'center_code');
    }

    public function loginLogs()
    {
        return $this->hasMany(ArchiveLoginLog::class, 'username', 'uname');
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

    // Accessors
    public function getFormattedLastLoginAttribute()
    {
        return $this->last_login ? $this->last_login->format('d M Y H:i') : 'Never';
    }
}