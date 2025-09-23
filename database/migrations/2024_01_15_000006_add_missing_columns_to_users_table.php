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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('status')->default('active')->after('last_name');
            $table->string('mobile_number', 15)->nullable()->after('status');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('mobile_number');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->text('address')->nullable()->after('date_of_birth');
            $table->string('avatar')->nullable()->after('address');
            $table->string('username')->nullable()->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'middle_name', 
                'last_name',
                'status',
                'mobile_number',
                'gender',
                'date_of_birth',
                'address',
                'avatar',
                'username'
            ]);
        });
    }
};
