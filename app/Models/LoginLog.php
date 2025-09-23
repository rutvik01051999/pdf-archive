<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $table = 'login_logs';
    
    protected $fillable = [
        'username',
        'center',
        'last_login'
    ];
    
    // No timestamps since table doesn't have created_at/updated_at
    public $timestamps = false;
    
    protected $casts = [
        'last_login' => 'datetime'
    ];
}
