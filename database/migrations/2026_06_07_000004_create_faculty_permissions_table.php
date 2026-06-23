<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('faculty_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('permission'); // 'guide', 'approval_faculty', 'noc_authority'
            $table->timestamps();

            $table->unique(['user_id', 'permission']);
        });

        // Migrate existing authority_type values
        $users = DB::table('users')->whereNotNull('authority_type')->get();
        foreach ($users as $user) {
            if (in_array($user->authority_type, ['guide', 'approval_faculty', 'noc_authority'])) {
                DB::table('faculty_permissions')->insert([
                    'user_id' => $user->id,
                    'permission' => $user->authority_type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Drop authority_type column
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['authority_type']);
            $table->dropColumn('authority_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('authority_type')->nullable();
            $table->index('authority_type');
        });

        // Restore authority_type from permissions (take the first one)
        $permissions = DB::table('faculty_permissions')->get()->groupBy('user_id');
        foreach ($permissions as $userId => $userPerms) {
            if ($userPerms->isNotEmpty()) {
                $firstPerm = $userPerms->first()->permission;
                DB::table('users')->where('id', $userId)->update([
                    'authority_type' => $firstPerm
                ]);
            }
        }

        Schema::dropIfExists('faculty_permissions');
    }
};
