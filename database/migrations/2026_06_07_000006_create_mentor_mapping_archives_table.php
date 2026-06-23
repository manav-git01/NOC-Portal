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
        Schema::create('mentor_mapping_archives', function (Blueprint $table) {
            $table->id();
            $table->timestamp('import_date');
            $table->foreignId('imported_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('file_name');
            $table->string('file_path')->nullable();
            $table->integer('total_students');
            $table->integer('total_guides');
            $table->integer('total_batches');
            $table->text('import_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('archived_mentor_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('archive_id')->constrained('mentor_mapping_archives')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->string('student_name');
            $table->string('enrollment_number');
            $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('set null');
            $table->string('batch_name')->nullable();
            $table->foreignId('guide_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('guide_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_mentor_mappings');
        Schema::dropIfExists('mentor_mapping_archives');
    }
};
