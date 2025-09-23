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
        Schema::table('certi_student', function (Blueprint $table) {
            $table->string('email')->nullable()->after('mobile_number');
            $table->string('phone_number', 15)->nullable()->after('email');
            $table->integer('batch_no')->default(1)->after('phone_number');
            
            // Add indexes for better performance
            $table->index('email');
            $table->index('batch_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certi_student', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['batch_no']);
            $table->dropColumn(['email', 'phone_number', 'batch_no']);
        });
    }
};
