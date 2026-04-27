<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class ResidenceCategorySeeder extends Seeder
{
    /**
     * Seed kategori untuk modul Hunian.
     * Mengisi tabel 'categories' dengan type = 'residence'.
     */
    public function run(): void
    {
        $categories = [
            'Kost Putra',
            'Kost Putri',
            'Kost Campur',
            'Kontrakan',
            'Apartemen',
            'Rumah Sewa',
            'Kost Eksklusif',
            'Lainnya',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['name' => $name, 'type' => 'residence']
            );
        }
    }
}
