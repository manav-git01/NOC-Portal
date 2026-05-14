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
        Schema::create('nocs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained('internship_applications')->onDelete('cascade');
            $table->string('noc_number')->unique();
            $table->string('pdf_path');
            $table->timestamp('generated_at');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nocs');
    }
};
