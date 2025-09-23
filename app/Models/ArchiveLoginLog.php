<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveLoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'center',
        'ip_address',
        'user_agent',
        'login_time'
    ];

    protected $casts = [
        'login_time' => 'datetime'
    ];

    // Relationships
    public function login()
    {
        return $this->belongsTo(ArchiveLogin::class, 'username', 'uname');
    }

    public function center()
    {
        return $this->belongsTo(ArchiveCenter::class, 'center', 'center_code');
    }

    // Scopes
    public function scopeByUsername($query, $username)
    {
        return $query->where('username', $username);
    }

    public function scopeByCenter($query, $center)
    {
        return $query->where('center', $center);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('login_time', '>=', now()->subDays($days));
    }
}