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
        Schema::create('archive_logins', function (Blueprint $table) {
            $table->id();
            $table->string('uname');
            $table->string('full_name')->nullable();
            $table->string('center');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
            
            $table->index(['uname', 'center']);
            $table->index(['center', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_logins');
    }
};