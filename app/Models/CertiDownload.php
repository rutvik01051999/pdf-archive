<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertiDownload extends Model
{
    protected $table = 'certi_downloads';
    
    protected $fillable = [
        'name',
        'mobile_number',
        'download_from',
        'created_at'
    ];
    
    public $timestamps = false;
}
