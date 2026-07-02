<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Hapus nilai 'rumah_sewa' dari enum residence_type.
     * Karena MySQL tidak support langsung MODIFY ENUM via Blueprint,
     * kita gunakan raw SQL ALTER TABLE.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE residences MODIFY COLUMN residence_type ENUM('kos', 'kontrakan', 'apartemen') NULL COMMENT 'kos=per kamar, kontrakan=per unit rumah, apartemen=per unit gedung'");
    }

    public function down(): void
    {
        // Kembalikan rumah_sewa ke dalam enum jika rollback
        DB::statement("ALTER TABLE residences MODIFY COLUMN residence_type ENUM('kos', 'kontrakan', 'apartemen', 'rumah_sewa') NULL COMMENT 'kos=per kamar, kontrakan=per unit rumah, apartemen=per unit gedung, rumah_sewa=per rumah'");
    }
};
