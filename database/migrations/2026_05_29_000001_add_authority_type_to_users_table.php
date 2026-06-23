<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds 'authority_type' to the users table to differentiate user authorities.
     *
     * Possible values:
     *  - 'guide'            : Guide faculty
     *  - 'approval_faculty' : Internship approval faculty
     *  - 'noc_authority'    : NOC generation / higher faculty
     *  - 'admin'            : Admin user
     *  - 'student'          : Student user
     *  - null               : Default for newly created faculty (acts as guide)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('authority_type')->nullable()->default(null)->after('status');
            $table->index('authority_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['authority_type']);
            $table->dropColumn('authority_type');
        });
    }
};
