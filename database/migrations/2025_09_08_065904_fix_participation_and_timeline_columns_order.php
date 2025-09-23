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
            // Drop existing columns if they exist
            $columnsToDrop = [];
            for ($i = 1; $i <= 4; $i++) {
                $columnsToDrop[] = 'participation_categories_' . $i;
                $columnsToDrop[] = 'timeline_' . $i;
            }
            
            // Filter out columns that don't exist to avoid errors
            $existingColumns = Schema::getColumnListing('main_contents');
            $columnsToDrop = array_intersect($columnsToDrop, $existingColumns);
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
            
            // Add columns in the correct order
            $afterColumn = 'image';
            
            // Add participation categories
            for ($i = 1; $i <= 4; $i++) {
                $columnName = 'participation_categories_' . $i;
                $table->string($columnName)->nullable()->after($afterColumn);
                $afterColumn = $columnName;
            }
            
            // Add timeline sections
            for ($i = 1; $i <= 4; $i++) {
                $columnName = 'timeline_' . $i;
                $table->string($columnName)->nullable()->after($afterColumn);
                $afterColumn = $columnName;
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a one-way migration since we're just fixing the order
        // You would need to manually revert the changes if needed
    }
};
