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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('state_id');
            $table->timestamps();
            
            // Add foreign key constraint
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            
            // Add indexes
            $table->index('name');
            $table->index('state_id');
            
            // Add unique constraint for city name within a state
            $table->unique(['name', 'state_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
