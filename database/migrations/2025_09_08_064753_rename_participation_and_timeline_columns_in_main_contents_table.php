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
            // Check if the old columns exist before renaming
            if (Schema::hasColumn('main_contents', 'participation_categories')) {
                $table->renameColumn('participation_categories', 'participation_categories_1');
            }
            
            if (Schema::hasColumn('main_contents', 'timeline')) {
                $table->renameColumn('timeline', 'timeline_1');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_contents', function (Blueprint $table) {
            // Revert the column renames
            if (Schema::hasColumn('main_contents', 'participation_categories_1')) {
                $table->renameColumn('participation_categories_1', 'participation_categories');
            }
            
            if (Schema::hasColumn('main_contents', 'timeline_1')) {
                $table->renameColumn('timeline_1', 'timeline');
            }
        });
    }
};
