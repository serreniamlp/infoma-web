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
        // Admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@infoma.com',
            'phone' => '081234567890',
            'address' => 'Jl. Admin No. 1, Jakarta',
        ]);
        $admin->roles()->attach(Role::where('name', 'admin')->first()->id);

        // Provider user
        $provider = User::factory()->create([
            'name' => 'Provider User',
            'email' => 'provider@infoma.com',
            'phone' => '081234567891',
            'address' => 'Jl. Provider No. 2, Jakarta',
        ]);
        $provider->roles()->attach(Role::where('name', 'provider')->first()->id);

        // Regular user
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@infoma.com',
            'phone' => '081234567892',
            'address' => 'Jl. User No. 3, Jakarta',
        ]);
        $user->roles()->attach(Role::where('name', 'user')->first()->id);

        // Test user (for development)
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567893',
            'address' => 'Jl. Test No. 123, Jakarta',
        ]);
        $testUser->roles()->attach(Role::where('name', 'user')->first()->id);
    }
}
