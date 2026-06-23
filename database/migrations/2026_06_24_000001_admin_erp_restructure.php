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
            $table->string('account_status')->default('active')->after('status');
            $table->boolean('is_locked')->default(false)->after('account_status');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('action_type')->nullable()->index()->after('action');
            $table->json('details')->nullable()->after('target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_status', 'is_locked']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['action_type', 'details']);
        });
    }
};
