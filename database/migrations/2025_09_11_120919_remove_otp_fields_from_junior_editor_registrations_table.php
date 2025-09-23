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
            // Remove OTP related fields
            $table->dropColumn(['otp', 'otp_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('junior_editor_registrations', function (Blueprint $table) {
            // Add back OTP fields if needed to rollback
            $table->string('otp')->nullable()->after('mobile_verified');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
        });
    }
};