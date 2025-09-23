<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('je011_razorpay_payments_transaction', 'razorpay_payments_transaction');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('razorpay_payments_transaction', 'je011_razorpay_payments_transaction');
    }
};
