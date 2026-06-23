<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class AddAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Admin',
                'description' => 'Administrator with access to all data and configurations',
            ]
        );
    }
}
