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
        Schema::create('je011_razorpay_payments_transaction', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 20)->nullable();
            $table->string('order_id', 200)->nullable();
            $table->string('entity', 200)->nullable();
            $table->string('amount', 200)->nullable();
            $table->string('amount_paid', 200)->nullable();
            $table->string('amount_due', 200)->nullable();
            $table->string('currency', 200)->nullable();
            $table->string('receipt', 200)->nullable();
            $table->string('status', 200)->nullable();
            $table->string('attempts', 200)->nullable();
            $table->string('created_at', 200)->nullable();
            $table->timestamp('create_date')->useCurrent();
            $table->timestamp('update_date')->useCurrent()->useCurrentOnUpdate();
            $table->integer('payment_update_api_check')->default(0);
            
            // Indexes
            $table->index('mobile');
            $table->index('amount_paid');
            $table->index('amount_due');
            $table->index('create_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('je011_razorpay_payments_transaction');
    }
};
