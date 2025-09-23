<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RazorpayPaymentTransaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'razorpay_payments_transaction';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mobile',
        'order_id',
        'entity',
        'amount',
        'amount_paid',
        'amount_due',
        'currency',
        'receipt',
        'status',
        'attempts',
        'created_at',
        'payment_update_api_check',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'create_date' => 'datetime',
        'update_date' => 'datetime',
        'payment_update_api_check' => 'integer',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'create_date',
        'update_date',
    ];
}
