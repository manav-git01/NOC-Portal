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
        Schema::table('internship_applications', function (Blueprint $table) {
            // Company Profile Fields
            $table->string('branch_address')->nullable()->after('company_address');
            $table->string('number_of_employees')->nullable()->after('branch_address');
            $table->text('branch_locations')->nullable()->after('number_of_employees');
            $table->text('head_office_address')->nullable()->after('branch_locations');
            
            // Contact Person Details
            $table->string('contact_person_name')->nullable()->after('head_office_address');
            $table->string('contact_person_phone')->nullable()->after('contact_person_name');
            $table->string('contact_person_email')->nullable()->after('contact_person_phone');
            
            // HR Details
            $table->string('hr_name')->nullable()->after('contact_person_email');
            $table->string('hr_phone')->nullable()->after('hr_name');
            $table->string('hr_email')->nullable()->after('hr_phone');
            
            // Company Details
            $table->text('technology')->nullable()->after('hr_email');
            $table->text('current_project')->nullable()->after('technology');
            $table->text('clients')->nullable()->after('current_project');
            $table->text('how_did_you_get_company')->nullable()->after('clients');
            $table->text('reason_to_select_company')->nullable()->after('how_did_you_get_company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_applications', function (Blueprint $table) {
            $table->dropColumn([
                'branch_address',
                'number_of_employees',
                'branch_locations',
                'head_office_address',
                'contact_person_name',
                'contact_person_phone',
                'contact_person_email',
                'hr_name',
                'hr_phone',
                'hr_email',
                'technology',
                'current_project',
                'clients',
                'how_did_you_get_company',
                'reason_to_select_company'
            ]);
        });
    }
};
