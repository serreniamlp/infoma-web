<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MarketplaceProduct;
use App\Models\ProductCategory;
use App\Models\User;

class MarketplaceProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $categories = ProductCategory::all();

        if ($users->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('No users or categories found. Please run User and ProductCategory seeders first.');
            return;
        }

        $sampleProducts = [
            [
                'name' => 'Laptop Gaming Asus ROG',
                'description' => 'Laptop gaming dengan spesifikasi tinggi, cocok untuk gaming dan kerja. Kondisi sangat baik, jarang digunakan.',
                'condition' => 'like_new',
                'price' => 15000000,
                'stock_quantity' => 1,
                'location' => 'Jakarta Selatan, DKI Jakarta',
                'images' => ['sample/laptop1.jpg', 'sample/laptop2.jpg'],
                'tags' => ['laptop', 'gaming', 'asus', 'rog'],
                'category_name' => 'Elektronik',
            ],
            [
                'name' => 'Sepatu Nike Air Max',
                'description' => 'Sepatu olahraga Nike Air Max, ukuran 42. Cocok untuk jogging dan olahraga sehari-hari.',
                'condition' => 'good',
                'price' => 800000,
                'stock_quantity' => 1,
                'location' => 'Bandung, Jawa Barat',
                'images' => ['sample/sepatu1.jpg'],
                'tags' => ['sepatu', 'nike', 'olahraga', 'air max'],
                'category_name' => 'Fashion',
            ],
            [
                'name' => 'Buku Programming Laravel',
                'description' => 'Buku panduan lengkap belajar Laravel untuk pemula hingga advanced. Edisi terbaru.',
                'condition' => 'new',
                'price' => 150000,
                'stock_quantity' => 3,
                'location' => 'Surabaya, Jawa Timur',
                'images' => ['sample/buku1.jpg'],
                'tags' => ['buku', 'programming', 'laravel', 'php'],
                'category_name' => 'Buku & Media',
            ],
            [
                'name' => 'Kursi Gaming RGB',
                'description' => 'Kursi gaming dengan lampu RGB, ergonomis dan nyaman untuk duduk lama.',
                'condition' => 'good',
                'price' => 2500000,
                'stock_quantity' => 1,
                'location' => 'Yogyakarta, DIY',
                'images' => ['sample/kursi1.jpg', 'sample/kursi2.jpg'],
                'tags' => ['kursi', 'gaming', 'rgb', 'ergonomis'],
                'category_name' => 'Rumah Tangga',
            ],
            [
                'name' => 'Smartphone iPhone 12',
                'description' => 'iPhone 12 128GB, warna biru. Kondisi sangat baik, baterai masih 95%.',
                'condition' => 'like_new',
                'price' => 8000000,
                'stock_quantity' => 1,
                'location' => 'Medan, Sumatera Utara',
                'images' => ['sample/iphone1.jpg', 'sample/iphone2.jpg'],
                'tags' => ['smartphone', 'iphone', 'apple', '12'],
                'category_name' => 'Elektronik',
            ],
            [
                'name' => 'Dumbbell Set 20kg',
                'description' => 'Set dumbbell 20kg untuk fitness di rumah. Lengkap dengan rack penyimpanan.',
                'condition' => 'good',
                'price' => 1200000,
                'stock_quantity' => 1,
                'location' => 'Semarang, Jawa Tengah',
                'images' => ['sample/dumbbell1.jpg'],
                'tags' => ['dumbbell', 'fitness', 'olahraga', 'gym'],
                'category_name' => 'Olahraga',
            ],
        ];

        foreach ($sampleProducts as $productData) {
            $category = $categories->where('name', $productData['category_name'])->first();
            $seller = $users->random();

            if ($category) {
                MarketplaceProduct::create([
                    'seller_id' => $seller->id,
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'condition' => $productData['condition'],
                    'price' => $productData['price'],
                    'stock_quantity' => $productData['stock_quantity'],
                    'location' => $productData['location'],
                    'images' => $productData['images'],
                    'tags' => $productData['tags'],
                    'status' => 'active',
                ]);
            }
        }

        $this->command->info('Sample marketplace products created successfully!');
    }
}
