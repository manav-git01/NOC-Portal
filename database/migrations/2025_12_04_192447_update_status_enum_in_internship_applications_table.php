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
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            \DB::statement("ALTER TABLE internship_applications CHANGE status status ENUM('pending', 'faculty_approved', 'faculty_rejected', 'pending_higher', 'higher_faculty_approved', 'higher_faculty_rejected', 'noc_generated') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            \DB::statement("ALTER TABLE internship_applications CHANGE status status ENUM('pending', 'faculty_approved', 'faculty_rejected', 'higher_faculty_approved', 'higher_faculty_rejected', 'noc_generated') DEFAULT 'pending'");
        }
    }
};
