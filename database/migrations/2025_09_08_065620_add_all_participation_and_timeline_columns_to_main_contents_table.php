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
        Schema::table('main_contents', function (Blueprint $table) {
            // Add participation categories
            for ($i = 1; $i <= 4; $i++) {
                $columnName = 'participation_categories_' . $i;
                if (!Schema::hasColumn('main_contents', $columnName)) {
                    $table->string($columnName)->nullable()->after('image');
                }
            }
            
            // Add timeline sections
            for ($i = 1; $i <= 4; $i++) {
                $columnName = 'timeline_' . $i;
                if (!Schema::hasColumn('main_contents', $columnName)) {
                    $table->string($columnName)->nullable()->after('participation_categories_4');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_contents', function (Blueprint $table) {
            // Drop all participation categories and timeline columns
            $columnsToDrop = [];
            
            for ($i = 1; $i <= 4; $i++) {
                $columnsToDrop[] = 'participation_categories_' . $i;
                $columnsToDrop[] = 'timeline_' . $i;
            }
            
            if (count($columnsToDrop) > 0) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
