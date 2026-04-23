<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Seed product categories
        $this->call(ProductCategorySeeder::class);

        // Create demo users
        $this->createDemoUsers();
    }

    private function createDemoUsers()
    {
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@infoma.com'],
            [
                'name'     => 'Admin User',
                'password' => bcrypt('password'),
                'phone'    => '081234567890',
                'address'  => 'Jl. Admin No. 1, Jakarta',
            ]
        );
        $admin->roles()->syncWithoutDetaching(
            Role::where('name', 'admin')->first()->id
        );

        // Provider Hunian
        $providerResidence = User::firstOrCreate(
            ['email' => 'provider.hunian@infoma.com'],
            [
                'name'     => 'Provider Hunian',
                'password' => bcrypt('password'),
                'phone'    => '081234567891',
                'address'  => 'Jl. Provider No. 2, Jakarta',
            ]
        );
        $providerResidence->roles()->syncWithoutDetaching(
            Role::where('name', 'provider_residence')->first()->id
        );

        // Provider Event
        $providerEvent = User::firstOrCreate(
            ['email' => 'provider.event@infoma.com'],
            [
                'name'     => 'Provider Event',
                'password' => bcrypt('password'),
                'phone'    => '081234567892',
                'address'  => 'Jl. Provider No. 3, Jakarta',
            ]
        );
        $providerEvent->roles()->syncWithoutDetaching(
            Role::where('name', 'provider_event')->first()->id
        );

        // Mahasiswa
        $user = User::firstOrCreate(
            ['email' => 'user@infoma.com'],
            [
                'name'     => 'Regular User',
                'password' => bcrypt('password'),
                'phone'    => '081234567893',
                'address'  => 'Jl. User No. 4, Jakarta',
            ]
        );
        $user->roles()->syncWithoutDetaching(
            Role::where('name', 'user')->first()->id
        );

        // Mahasiswa Seller
        $seller = User::firstOrCreate(
            ['email' => 'seller@infoma.com'],
            [
                'name'      => 'Mahasiswa Seller',
                'password'  => bcrypt('password'),
                'phone'     => '081234567894',
                'address'   => 'Jl. Seller No. 5, Jakarta',
                'is_seller' => true,
            ]
        );
        $seller->roles()->syncWithoutDetaching(
            Role::where('name', 'user')->first()->id
        );
    }
}
