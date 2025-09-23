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
        Schema::create('pdf_archives', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('category')->nullable();
            $table->string('center')->nullable();
            $table->string('edition_name')->nullable();
            $table->string('edition_code')->nullable();
            $table->integer('page_number')->nullable();
            $table->string('pdf_file_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('google_cloud_path')->nullable();
            $table->string('google_cloud_thumbnail_path')->nullable();
            $table->string('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->integer('is_matrix_edition')->default(0);
            $table->integer('auto_generated')->default(0);
            $table->string('uploaded_by')->nullable();
            $table->timestamp('upload_date')->nullable();
            $table->integer('status')->default(1);
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->index(['center', 'category']);
            $table->index(['upload_date']);
            $table->index(['edition_name', 'edition_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_archives');
    }
};