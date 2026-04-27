<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Durasi sewa dalam bulan (khusus residence)
            $table->unsignedInteger('duration_months')->default(1)->after('check_out_date');
            // Total harga sudah dikali durasi (disimpan saat booking dibuat)
            $table->decimal('total_price', 12, 2)->default(0)->after('duration_months');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['duration_months', 'total_price']);
        });
    }
};
