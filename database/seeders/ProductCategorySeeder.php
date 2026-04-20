<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Elektronik',
                'description' => 'Perangkat elektronik dan gadget',
                'icon' => 'fas fa-laptop',
                'is_active' => true,
            ],
            [
                'name' => 'Fashion',
                'description' => 'Pakaian, sepatu, dan aksesoris',
                'icon' => 'fas fa-tshirt',
                'is_active' => true,
            ],
            [
                'name' => 'Rumah Tangga',
                'description' => 'Perabotan dan perlengkapan rumah',
                'icon' => 'fas fa-home',
                'is_active' => true,
            ],
            [
                'name' => 'Olahraga',
                'description' => 'Perlengkapan olahraga dan fitness',
                'icon' => 'fas fa-dumbbell',
                'is_active' => true,
            ],
            [
                'name' => 'Buku & Media',
                'description' => 'Buku, majalah, dan media lainnya',
                'icon' => 'fas fa-book',
                'is_active' => true,
            ],
            [
                'name' => 'Kesehatan & Kecantikan',
                'description' => 'Produk kesehatan dan kecantikan',
                'icon' => 'fas fa-heart',
                'is_active' => true,
            ],
            [
                'name' => 'Otomotif',
                'description' => 'Spare part dan aksesoris kendaraan',
                'icon' => 'fas fa-car',
                'is_active' => true,
            ],
            [
                'name' => 'Hobi & Koleksi',
                'description' => 'Barang koleksi dan hobi',
                'icon' => 'fas fa-puzzle-piece',
                'is_active' => true,
            ],
            [
                'name' => 'Makanan & Minuman',
                'description' => 'Makanan dan minuman kemasan',
                'icon' => 'fas fa-utensils',
                'is_active' => true,
            ],
            [
                'name' => 'Lainnya',
                'description' => 'Kategori lainnya',
                'icon' => 'fas fa-ellipsis-h',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }
    }
}
