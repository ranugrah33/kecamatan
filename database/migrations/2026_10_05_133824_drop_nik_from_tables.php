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
            if (Schema::hasColumn('users', 'nik')) {
                $table->dropColumn('nik');
            }
        });
        
        Schema::table('inventory_borrowings', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_borrowings', 'nik')) {
                $table->dropColumn('nik');
            }
        });
        
        Schema::table('certificate_requests', function (Blueprint $table) {
            if (Schema::hasColumn('certificate_requests', 'nik')) {
                $table->dropColumn('nik');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik')->nullable();
        });
        
        Schema::table('inventory_borrowings', function (Blueprint $table) {
            $table->string('nik')->nullable();
        });
        
        Schema::table('certificate_requests', function (Blueprint $table) {
            $table->string('nik')->nullable();
        });
    }
};
