<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name'         => 'admin',
                'display_name' => 'Administrator',
                'description'  => 'System administrator with full access',
            ],
            [
                'name'         => 'provider_residence',
                'display_name' => 'Provider Hunian',
                'description'  => 'Penyedia hunian (kost/kontrakan) untuk mahasiswa',
            ],
            [
                'name'         => 'provider_event',
                'display_name' => 'Provider Event',
                'description'  => 'Penyelenggara event dan kegiatan kampus',
            ],
            [
                'name'         => 'user',
                'display_name' => 'Mahasiswa',
                'description'  => 'Mahasiswa yang dapat booking hunian, mendaftar event, dan berjualan di marketplace',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}