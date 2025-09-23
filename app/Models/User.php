<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Spatie\Activitylog\Traits\LogsActivity;
// use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, FilterableByDates; // LogsActivity commented out

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'center',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_login' => 'datetime',
        ];
    }

    // No timestamps since table doesn't have created_at/updated_at
    public $timestamps = false;

    protected $appends = ['admin_full_name'];

    // Activity logging commented out - activity_log table doesn't exist
    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logOnly(['first_name', 'middle_name', 'last_name', 'email', 'status', 'mobile_number', 'gender', 'date_of_birth', 'address', 'avatar', 'username', 'state_id', 'city_id']);
    // }

    /**
     * The accessors to append to the model's array form.
     * 
     * @var list<string>
     */
    // public function getFullNameAttribute(): string
    // {
    //     return "{$this->first_name} {$this->middle_name} {$this->last_name}";
    // }

    /**
     * Get the admin full name from the username.
     * 
     * @return string
     */
    public function getAdminFullNameAttribute(): string
    {
        return $this->username ?? '';
    }

    // Create unique username on user creation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Username is already provided in the existing structure
        });
    }

    // Role functionality removed - no role checking required

    // Removed state and city relationships since they don't exist in the current table structure
}
