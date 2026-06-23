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
        Schema::create('internship_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('company_name');
            $table->text('company_address');
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_website')->nullable();
            $table->string('internship_position');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('internship_description')->nullable();
            $table->string('company_letter_path')->nullable(); // uploaded document
            $table->string('additional_documents')->nullable(); // JSON array of paths
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                $table->string('status')->default('pending');
            } else {
                $table->enum('status', ['pending', 'faculty_approved', 'faculty_rejected', 'higher_faculty_approved', 'higher_faculty_rejected', 'noc_generated'])->default('pending');
            }
            $table->text('faculty_remarks')->nullable();
            $table->text('higher_faculty_remarks')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('faculty_reviewed_at')->nullable();
            $table->timestamp('higher_faculty_reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_applications');
    }
};
