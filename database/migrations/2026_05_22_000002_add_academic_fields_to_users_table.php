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
            $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('set null');
            $table->foreignId('guide_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('faculty_id')->nullable()->unique();
            $table->string('designation')->nullable();
            $table->string('status')->nullable()->default('Active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['guide_id']);
            $table->dropColumn(['batch_id', 'guide_id', 'faculty_id', 'designation', 'status']);
        });
    }
};
