<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class ResidenceCategorySeeder extends Seeder
{
    /**
     * Seed kategori hunian.
     *
     * Setelah revisi, tipe hunian utama ditentukan via kolom `residence_type`
     * di tabel residences — bukan dari nama kategori.
     * Kategori di sini dipakai untuk sub-klasifikasi tambahan jika diperlukan.
     *
     * Mapping residence_type → kategori yang relevan:
     *   kos        → Kos Putra, Kos Putri, Kos Campur, Kos Eksklusif
     *   kontrakan  → Kontrakan
     *   apartemen  → Apartemen
     *   rumah_sewa → Rumah Sewa
     */
    public function run(): void
    {
        $categories = [
            // Kos
            ['name' => 'Kos Putra',    'type' => 'residence'],
            ['name' => 'Kos Putri',    'type' => 'residence'],
            ['name' => 'Kos Campur',   'type' => 'residence'],
            ['name' => 'Kos Eksklusif','type' => 'residence'],
            // Kontrakan
            ['name' => 'Kontrakan',    'type' => 'residence'],
            // Apartemen
            ['name' => 'Apartemen',    'type' => 'residence'],
            // Rumah Sewa
            ['name' => 'Rumah Sewa',   'type' => 'residence'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['name' => $cat['name'], 'type' => $cat['type']]
            );
        }
    }
}