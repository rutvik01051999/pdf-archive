<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    protected $table = 'login';
    
    protected $fillable = [
        'uname',
        'pass',
        'full_name',
        'center'
    ];
    
    // No timestamps since table doesn't have created_at/updated_at
    public $timestamps = false;
}
