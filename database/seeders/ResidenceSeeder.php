<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class ResidenceSeeder extends Seeder{
    public function run(): void{
        $residences = [
            // Isi disini
        ];

        foreach ($residences as $residence) {
            \App\Models\Residence::create($residence);
        }
    }
}