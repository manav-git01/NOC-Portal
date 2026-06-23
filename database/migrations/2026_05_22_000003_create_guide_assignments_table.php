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
        Schema::create('guide_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('guide_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('unassigned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_assignments');
    }
};
