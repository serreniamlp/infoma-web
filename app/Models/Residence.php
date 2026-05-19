<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Residence extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'category_id',
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'rental_period',
        'price',
        'capacity',
        'available_slots',
        'facilities',
        'images',
        'discount_type',
        'discount_value',
        'is_active',
        // ── Tipe & field spesifik (baru) ───────────────────────────
        'residence_type',   // kos | kontrakan | apartemen | rumah_sewa
        'kos_type',         // putra | putri | campur         → khusus kos
        'room_size',        // m²                             → kos & apartemen
        'bedroom_count',    // jumlah kamar tidur             → kontrakan, rumah_sewa, apartemen
        'bathroom_count',   // jumlah kamar mandi             → kontrakan, rumah_sewa, apartemen
        'building_size',    // luas bangunan m²               → kontrakan & rumah_sewa
        'land_size',        // luas tanah m²                  → kontrakan & rumah_sewa
        'unit_type',        // studio|1BR|2BR|3BR             → apartemen
        'floor_number',     // lantai                         → apartemen
        'tower_name',       // nama tower/gedung              → apartemen
        'furnish_status',   // unfurnished|semi|full          → semua
    ];

    protected $casts = [
        'facilities'     => 'array',
        'images'         => 'array',
        'is_active'      => 'boolean',
        'latitude'       => 'decimal:8',
        'longitude'      => 'decimal:8',
        'room_size'      => 'decimal:2',
        'building_size'  => 'decimal:2',
        'land_size'      => 'decimal:2',
        'bedroom_count'  => 'integer',
        'bathroom_count' => 'integer',
        'floor_number'   => 'integer',
    ];

    // ── HELPER: Tipe hunian ─────────────────────────────────────────

    public function isKos(): bool
    {
        return $this->residence_type === 'kos';
    }

    public function isKontrakan(): bool
    {
        return $this->residence_type === 'kontrakan';
    }

    public function isApartemen(): bool
    {
        return $this->residence_type === 'apartemen';
    }

    public function isRumahSewa(): bool
    {
        return $this->residence_type === 'rumah_sewa';
    }

    /**
     * Label tipe hunian untuk ditampilkan di UI.
     */
    public function getResidenceTypeLabelAttribute(): string
    {
        return match($this->residence_type) {
            'kos'        => 'Kos',
            'kontrakan'  => 'Kontrakan',
            'apartemen'  => 'Apartemen',
            'rumah_sewa' => 'Rumah Sewa',
            default      => 'Hunian',
        };
    }

    /**
     * Label jenis kos (putra/putri/campur).
     */
    public function getKosTypeLabelAttribute(): string
    {
        return match($this->kos_type) {
            'putra'  => 'Putra',
            'putri'  => 'Putri',
            'campur' => 'Campur',
            default  => '-',
        };
    }

    /**
     * Label status furnitur.
     */
    public function getFurnishStatusLabelAttribute(): string
    {
        return match($this->furnish_status) {
            'unfurnished'    => 'Unfurnished',
            'semi_furnished' => 'Semi Furnished',
            'full_furnished' => 'Full Furnished',
            default          => '-',
        };
    }

    /**
     * Label kapasitas yang kontekstual per tipe.
     * Kos: "kamar tersedia", Kontrakan/Apartemen/Rumah: "unit tersedia"
     */
    public function getCapacityLabelAttribute(): string
    {
        return $this->isKos() ? 'Kamar Tersedia' : 'Unit Tersedia';
    }

    /**
     * Label satuan booking yang kontekstual.
     */
    public function getBookingUnitLabelAttribute(): string
    {
        return $this->isKos() ? 'kamar' : 'unit';
    }

    // ── PRICING ────────────────────────────────────────────────────

    public function getPricePerMonthAttribute()
    {
        return $this->attributes['price'] ?? null;
    }

    public function getDiscountedPrice(): float
    {
        $basePrice = (float) ($this->price ?? 0);

        if (!$this->discount_type || $this->discount_value === null) {
            return $basePrice;
        }

        if ($this->discount_type === 'percentage') {
            $discount = $basePrice * ((float) $this->discount_value) / 100.0;
            return max(0.0, $basePrice - $discount);
        }

        return max(0.0, $basePrice - (float) $this->discount_value);
    }

    // ── RELATIONS ───────────────────────────────────────────────────

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings()
    {
        return $this->morphMany(Booking::class, 'bookable');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }
}