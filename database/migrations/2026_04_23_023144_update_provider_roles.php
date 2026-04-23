<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah role baru
        DB::table('roles')->insert([
            [
                'name'         => 'provider_residence',
                'display_name' => 'Provider Hunian',
                'description'  => 'Penyedia hunian (kost/kontrakan) untuk mahasiswa',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'name'         => 'provider_event',
                'display_name' => 'Provider Event',
                'description'  => 'Penyelenggara event dan kegiatan kampus',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);

        // Migrate user yang punya role 'provider' lama
        // ke role 'provider_residence' dan 'provider_event' sekaligus
        // (karena kita tidak tahu mereka provider jenis apa)
        $oldRole = DB::table('roles')->where('name', 'provider')->first();
        $newRoleResidence = DB::table('roles')->where('name', 'provider_residence')->first();
        $newRoleEvent = DB::table('roles')->where('name', 'provider_event')->first();

        if ($oldRole) {
            // Ambil semua user yang punya role provider lama
            $providerUserIds = DB::table('user_roles')
                ->where('role_id', $oldRole->id)
                ->pluck('user_id');

            foreach ($providerUserIds as $userId) {
                // Assign kedua role baru
                DB::table('user_roles')->insertOrIgnore([
                    ['user_id' => $userId, 'role_id' => $newRoleResidence->id, 'created_at' => now(), 'updated_at' => now()],
                    ['user_id' => $userId, 'role_id' => $newRoleEvent->id, 'created_at' => now(), 'updated_at' => now()],
                ]);
            }

            // Hapus user_roles lama yang pakai role provider
            DB::table('user_roles')->where('role_id', $oldRole->id)->delete();

            // Hapus role provider lama
            DB::table('roles')->where('name', 'provider')->delete();
        }
    }

    public function down(): void
    {
        // Kembalikan role provider lama
        DB::table('roles')->insertOrIgnore([
            [
                'name'         => 'provider',
                'display_name' => 'Provider',
                'description'  => 'Service provider who offers residences and activities',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);

        // Hapus role baru
        DB::table('roles')->whereIn('name', ['provider_residence', 'provider_event'])->delete();
    }
};