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
        Schema::create('certificate_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('request_code')->unique();
            $table->string('applicant_name');
            $table->string('nik')->nullable();
            $table->string('phone')->nullable();
            $table->string('document_type'); // Sertifikat or Piagam
            $table->string('activity_name');
            $table->string('activity_theme')->nullable();
            $table->date('activity_date');
            $table->string('activity_place');
            $table->text('purpose');
            $table->text('description')->nullable();
            $table->string('application_file')->nullable();
            $table->string('status')->default('Diajukan');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_requests');
    }
};
