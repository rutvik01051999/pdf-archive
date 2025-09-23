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
        Schema::table('junior_editor_registrations', function (Blueprint $table) {
            // Make all fields nullable except id, timestamps, and mobile_number
            $table->string('parent_name')->nullable()->change();
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->date('birth_date')->nullable()->change();
            $table->enum('gender', ['male', 'female'])->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->string('pincode', 6)->nullable()->change();
            $table->string('state')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('school_name')->nullable()->change();
            $table->string('school_telephone', 12)->nullable()->change();
            $table->enum('school_class', ['4', '5', '6', '7', '8', '9', '10', '11', '12'])->nullable()->change();
            $table->text('school_address')->nullable()->change();
            $table->enum('delivery_type', ['Door Step Delivery', 'Self Pick Up'])->nullable()->change();
            $table->decimal('amount', 8, 2)->nullable()->change();
            $table->string('pickup_centers')->nullable()->change();
            $table->text('office_address')->nullable()->change();
            $table->string('from_source')->nullable()->change();
            $table->boolean('mobile_verified')->nullable()->change();
            $table->string('razorpay_order_id')->nullable()->change();
            $table->string('razorpay_payment_id')->nullable()->change();
            $table->string('razorpay_signature')->nullable()->change();
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('junior_editor_registrations', function (Blueprint $table) {
            // Revert fields back to not nullable (except those that should remain nullable)
            $table->string('parent_name')->nullable(false)->change();
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->date('birth_date')->nullable(false)->change();
            $table->enum('gender', ['male', 'female'])->nullable(false)->change();
            $table->text('address')->nullable(false)->change();
            $table->string('pincode', 6)->nullable(false)->change();
            $table->string('state')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('school_name')->nullable(false)->change();
            $table->string('school_telephone', 12)->nullable(false)->change();
            $table->enum('school_class', ['4', '5', '6', '7', '8', '9', '10', '11', '12'])->nullable(false)->change();
            $table->text('school_address')->nullable(false)->change();
            $table->enum('delivery_type', ['Door Step Delivery', 'Self Pick Up'])->nullable(false)->change();
            $table->decimal('amount', 8, 2)->nullable(false)->change();
            $table->string('pickup_centers')->nullable(false)->change();
            $table->text('office_address')->nullable(false)->change();
            $table->string('from_source')->nullable(false)->change();
            $table->boolean('mobile_verified')->nullable(false)->change();
            $table->string('razorpay_order_id')->nullable(false)->change();
            $table->string('razorpay_payment_id')->nullable(false)->change();
            $table->string('razorpay_signature')->nullable(false)->change();
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->nullable(false)->change();
        });
    }
};