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
        Schema::create('razorpay_payment_response', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 15)->nullable();
            $table->string('razorpay_payment_id', 100)->nullable();
            $table->string('razorpay_order_id', 100)->nullable();
            $table->string('razorpay_signature', 100)->nullable();
            $table->integer('status')->default(1);
            $table->string('date', 10)->nullable();
            $table->timestamp('create_date')->nullable()->useCurrent();
            $table->timestamp('update_date')->nullable()->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razorpay_payment_response');
    }
};
