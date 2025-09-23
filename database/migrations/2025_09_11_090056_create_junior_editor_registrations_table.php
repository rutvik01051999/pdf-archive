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
        Schema::create('junior_editor_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('parent_name');
            $table->string('mobile_number', 10);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female']);
            $table->text('address');
            $table->string('pincode', 6);
            $table->string('state');
            $table->string('city');
            $table->string('school_name');
            $table->string('school_telephone', 12);
            $table->enum('school_class', ['4', '5', '6', '7', '8', '9', '10', '11', '12']);
            $table->text('school_address');
            $table->enum('delivery_type', ['Door Step Delivery', 'Self Pick Up']);
            $table->decimal('amount', 8, 2);
            $table->string('pickup_centers')->nullable();
            $table->text('office_address')->nullable();
            $table->string('from_source')->default('direct');
            $table->boolean('mobile_verified')->default(false);
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamps();
            
            $table->index(['mobile_number', 'email']);
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('junior_editor_registrations');
    }
};