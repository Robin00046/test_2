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
            //
            // Uncomment the line below to add a 'role_name' column to the 'users' table
            $table->enum('role_name', ['admin', 'operator'])
                ->default('operator') // Default value can be set to 'user' or any other role
                ->after('email'); // Adjust the position as needed
            $table->string('username')->unique()->after('role_name'); // Add username column after role_name
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //  Uncomment the line below to drop the 'role_name' column from the 'users' table
            $table->dropColumn('role_name');
            $table->dropColumn('username'); // Drop username column if it exists
        });
    }
};
