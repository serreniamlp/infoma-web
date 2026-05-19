<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('residences', function (Blueprint $table) {
            // Tipe hunian utama — menentukan field mana yang relevan
            $table->enum('residence_type', ['kos', 'kontrakan', 'apartemen', 'rumah_sewa'])
                ->nullable()
                ->after('category_id')
                ->comment('kos=per kamar, kontrakan=per unit rumah, apartemen=per unit gedung, rumah_sewa=per rumah');

            // ── KHUSUS KOS ───────────────────────────────────────────────
            // Jenis penghuni kos
            $table->enum('kos_type', ['putra', 'putri', 'campur'])
                ->nullable()
                ->after('residence_type');

            // ── KOS & APARTEMEN ──────────────────────────────────────────
            // Ukuran kamar/unit dalam m²
            $table->decimal('room_size', 8, 2)
                ->nullable()
                ->after('kos_type')
                ->comment('Luas kamar (kos) atau unit (apartemen) dalam m²');

            // ── KONTRAKAN, RUMAH SEWA & APARTEMEN ───────────────────────
            // Jumlah kamar tidur
            $table->tinyInteger('bedroom_count')
                ->nullable()
                ->unsigned()
                ->after('room_size')
                ->comment('Jumlah kamar tidur');

            // Jumlah kamar mandi
            $table->tinyInteger('bathroom_count')
                ->nullable()
                ->unsigned()
                ->after('bedroom_count')
                ->comment('Jumlah kamar mandi');

            // ── KONTRAKAN & RUMAH SEWA ───────────────────────────────────
            // Luas bangunan dalam m²
            $table->decimal('building_size', 8, 2)
                ->nullable()
                ->after('bathroom_count')
                ->comment('Luas bangunan dalam m²');

            // Luas tanah dalam m²
            $table->decimal('land_size', 8, 2)
                ->nullable()
                ->after('building_size')
                ->comment('Luas tanah dalam m²');

            // ── APARTEMEN ────────────────────────────────────────────────
            // Tipe unit apartemen
            $table->string('unit_type', 20)
                ->nullable()
                ->after('land_size')
                ->comment('studio, 1BR, 2BR, 3BR, dll');

            // Nomor lantai unit
            $table->smallInteger('floor_number')
                ->nullable()
                ->unsigned()
                ->after('unit_type')
                ->comment('Lantai unit apartemen');

            // Nama tower/gedung
            $table->string('tower_name', 100)
                ->nullable()
                ->after('floor_number')
                ->comment('Nama tower atau gedung apartemen');

            // ── SEMUA TIPE ───────────────────────────────────────────────
            // Status furnitur
            $table->enum('furnish_status', ['unfurnished', 'semi_furnished', 'full_furnished'])
                ->nullable()
                ->after('tower_name')
                ->comment('Status perabot hunian');
        });
    }

    public function down(): void
    {
        Schema::table('residences', function (Blueprint $table) {
            $table->dropColumn([
                'residence_type',
                'kos_type',
                'room_size',
                'bedroom_count',
                'bathroom_count',
                'building_size',
                'land_size',
                'unit_type',
                'floor_number',
                'tower_name',
                'furnish_status',
            ]);
        });
    }
};