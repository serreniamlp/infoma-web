<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'System administrator with full access'],
            ['name' => 'provider', 'display_name' => 'Provider', 'description' => 'Service provider who offers residences, marketplace products and activities'],
            ['name' => 'user', 'display_name' => 'User', 'description' => 'Regular user who can book residences, marketplace products and activities'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}