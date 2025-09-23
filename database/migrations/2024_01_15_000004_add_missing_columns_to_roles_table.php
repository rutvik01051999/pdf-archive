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
        Schema::table('roles', function (Blueprint $table) {
            // Add missing columns for roles
            $table->string('slug')->unique()->after('guard_name');
            $table->string('display_name')->after('slug');
            $table->text('description')->nullable()->after('display_name');
            
            // Add indexes
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['slug', 'display_name', 'description']);
        });
    }
};
