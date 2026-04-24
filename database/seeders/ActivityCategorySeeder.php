<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class ActivityCategorySeeder extends Seeder
{
    /**
     * Seed kategori untuk modul Event/Kegiatan.
     * Mengisi tabel 'categories' dengan type = 'activity'.
     */
    public function run(): void
    {
        $categories = [
            'Seminar & Workshop',
            'Lomba & Kompetisi',
            'Seni & Budaya',
            'Olahraga',
            'Sosial & Komunitas',
            'Teknologi & IT',
            'Bisnis & Kewirausahaan',
            'Kesehatan & Wellness',
            'Pendidikan',
            'Hiburan',
            'Lainnya',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['name' => $name, 'type' => 'activity']
            );
        }
    }
}
