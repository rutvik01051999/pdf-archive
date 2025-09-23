<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('process_steps', function (Blueprint $table) {
            // Add new content field
            $table->longText('content')->nullable()->after('process_id');
        });
        
        // Migrate existing data: combine sub_title and description into content
        DB::statement("
            UPDATE process_steps 
            SET content = CASE 
                WHEN sub_title IS NOT NULL AND description IS NOT NULL 
                THEN CONCAT('<h4>', sub_title, '</h4><p>', description, '</p>')
                WHEN sub_title IS NOT NULL 
                THEN CONCAT('<h4>', sub_title, '</h4>')
                WHEN description IS NOT NULL 
                THEN CONCAT('<p>', description, '</p>')
                ELSE NULL
            END
        ");
        
        Schema::table('process_steps', function (Blueprint $table) {
            // Drop old fields
            $table->dropColumn(['sub_title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_steps', function (Blueprint $table) {
            // Add back old fields
            $table->string('sub_title')->nullable()->after('process_id');
            $table->text('description')->nullable()->after('sub_title');
        });
        
        // Note: We cannot easily reverse the content migration as we'd need to parse HTML
        // This is a one-way migration for data structure improvement
        
        Schema::table('process_steps', function (Blueprint $table) {
            // Drop the content field
            $table->dropColumn('content');
        });
    }
};
