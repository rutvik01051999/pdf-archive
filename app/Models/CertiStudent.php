<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertiStudent extends Model
{
    protected $table = 'certi_student';
    
    protected $fillable = [
        'name',
        'mobile_number',
        'created_date'
    ];
    
    protected $casts = [
        'created_date' => 'datetime'
    ];
    
    public $timestamps = false; // Since we're using created_date instead of timestamps
}
