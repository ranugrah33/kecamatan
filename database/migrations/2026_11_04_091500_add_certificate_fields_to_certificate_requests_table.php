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
        Schema::table('certificate_requests', function (Blueprint $table) {
            $table->string('certificate_name')->nullable()->after('applicant_name');
            $table->text('certificate_text')->nullable()->after('activity_place');
            $table->string('nomor_sertifikat')->nullable()->after('request_code');
            $table->string('file_sertifikat_final')->nullable()->after('application_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_requests', function (Blueprint $table) {
            $table->dropColumn([
                'certificate_name',
                'certificate_text',
                'nomor_sertifikat',
                'file_sertifikat_final',
            ]);
        });
    }
};

