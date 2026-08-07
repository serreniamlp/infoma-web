<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MarketplaceProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'description',
        'condition',
        'price',
        'stock_quantity',
        'available_slots',
        'location',
        'latitude',
        'longitude',
        'images',
        'tags',
        'status',
        'views_count',
        'sold_at',
        'pickup_methods',    // ← BARU: array metode yang seller aktifkan
        'pickup_address',    // ← BARU: alamat pickup jika metode "ambil sendiri" aktif
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'latitude'       => 'decimal:8',
        'longitude'      => 'decimal:8',
        'images'         => 'array',
        'tags'           => 'array',
        'sold_at'        => 'datetime',
        'pickup_methods' => 'array',   // ← BARU
    ];

    protected $appends = [
        'condition_label',
        'status_label',
        'main_image',
        'rating_average',
        'is_available',
        'discount_percentage',
    ];

    // ── PICKUP METHOD HELPERS (BARU) ─────────────────────────────────────

    /**
     * Daftar semua metode yang tersedia di sistem beserta labelnya.
     */
    public static function availablePickupMethods(): array
    {
        return [
            'cod'      => [
                'label'       => 'COD (Bayar di Tempat)',
                'icon'        => 'fa-money-bill-wave',
                'color'       => 'green',
                'description' => 'Pembeli bertemu dengan penjual, bayar langsung saat menerima barang.',
                'need_address'=> false, // buyer tidak perlu isi alamat tujuan
            ],
            'delivery' => [
                'label'       => 'Diantar',
                'icon'        => 'fa-motorcycle',
                'color'       => 'blue',
                'description' => 'Barang diantar ke alamat pembeli (via kurir / penjual). Pembeli melunasi pembayaran online terlebih dahulu.',
                'need_address'=> true,  // buyer wajib isi alamat tujuan
            ],
            'pickup'   => [
                'label'       => 'Ambil Sendiri',
                'icon'        => 'fa-walking',
                'color'       => 'orange',
                'description' => 'Pembeli mendatangi alamat penjual untuk mengambil barang dan bayar langsung di lokasi.',
                'need_address'=> false, // buyer tidak perlu isi alamat tujuan
            ],
        ];
    }

    /**
     * Ambil metode yang seller aktifkan untuk produk ini.
     * Return array of ['cod' => [...], 'delivery' => [...], ...]
     */
    public function getActivePickupMethods(): array
    {
        $all     = self::availablePickupMethods();
        $active  = $this->pickup_methods ?? [];

        return array_filter($all, fn($key) => in_array($key, $active), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Apakah metode tertentu tersedia untuk produk ini?
     */
    public function hasPickupMethod(string $method): bool
    {
        return in_array($method, $this->pickup_methods ?? []);
    }

    /**
     * Apakah ada metode yang butuh alamat tujuan dari buyer?
     */
    public function hasDeliveryMethod(): bool
    {
        return $this->hasPickupMethod('delivery');
    }

    /**
     * Apakah COD tersedia?
     */
    public function hasCod(): bool
    {
        return $this->hasPickupMethod('cod');
    }

    /**
     * Label pickup methods yang aktif untuk ditampilkan.
     */
    public function getPickupMethodsLabelAttribute(): string
    {
        $active = $this->getActivePickupMethods();
        if (empty($active)) return '-';

        return implode(', ', array_column($active, 'label'));
    }

    // ── RELATIONS ────────────────────────────────────────────────────────

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MarketplaceTransaction::class, 'product_id');
    }

    /**
     * Total kuantitas barang yang terkunci dalam transaksi aktif (belum selesai/batal).
     */
    public function getActiveReservedQuantityAttribute(): int
    {
        return (int) $this->transactions()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->sum('quantity');
    }

    /**
     * Stok bebas yang belum terpakai oleh transaksi aktif.
     */
    public function getAvailableStockAttribute(): int
    {
        return max(0, (int) $this->stock_quantity - $this->active_reserved_quantity);
    }

    /**
     * Cek apakah produk ini memiliki transaksi aktif (sedang berjalan/belum selesai atau belum batal).
     */
    public function hasActiveTransactions(): bool
    {
        return $this->active_reserved_quantity > 0;
    }

    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function views(): HasMany
    {
        return $this->hasMany(MarketplaceProductView::class, 'product_id');
    }

    // ── SCOPES ───────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')->where('stock_quantity', '>', 0);
    }

    public function scopeBySeller($query, $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%')
                ->orWhereJsonContains('tags', $search);
        });
    }

    public function scopeByCondition($query, $condition)
    {
        return $query->where('condition', $condition);
    }

    public function scopePriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) $query->where('price', '>=', $minPrice);
        if ($maxPrice !== null) $query->where('price', '<=', $maxPrice);
        return $query;
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', '%' . $location . '%');
    }

    // ── ACCESSORS ────────────────────────────────────────────────────────

    public function getConditionLabelAttribute()
    {
        return [
            'new'          => 'Baru',
            'like_new'     => 'Seperti Baru',
            'good'         => 'Baik',
            'fair'         => 'Cukup',
            'needs_repair' => 'Perlu Perbaikan',
        ][$this->condition] ?? $this->condition;
    }

    public function getStatusLabelAttribute()
    {
        return [
            'draft'            => 'Draft',
            'active'           => 'Aktif',
            'sold'             => 'Terjual',
            'inactive'         => 'Tidak Aktif',
            'pending_approval' => 'Menunggu Persetujuan',
        ][$this->status] ?? $this->status;
    }

    public function getMainImageAttribute()
    {
        if (!empty($this->images)) {
            return asset('storage/' . $this->images[0]);
        }
        return asset('images/no-image.png');
    }

    public function getImageUrlsAttribute()
    {
        if (!empty($this->images)) {
            return collect($this->images)->map(fn($img) => asset('storage/' . $img))->toArray();
        }
        return [asset('images/no-image.png')];
    }

    public function getRatingAverageAttribute()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    public function getRatingsCountAttribute()
    {
        return $this->ratings()->count();
    }

    public function getIsAvailableAttribute()
    {
        return $this->status === 'active' && $this->stock_quantity > 0;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->original_price && $this->original_price > $this->price) {
            return round((($this->original_price - $this->price) / $this->original_price) * 100);
        }
        return 0;
    }

    // ── METHODS ──────────────────────────────────────────────────────────

    public function isBookmarkedBy($userId)
    {
        return $this->bookmarks()->where('user_id', $userId)->exists();
    }

    public function isBoughtBy($userId)
    {
        return $this->transactions()->where('buyer_id', $userId)->where('status', 'completed')->exists();
    }

    public function getSoldQuantityAttribute()
    {
        return $this->transactions()->where('status', 'completed')->sum('quantity');
    }

    public function markAsSold()
    {
        $this->update(['status' => 'sold', 'sold_at' => now(), 'stock_quantity' => 0, 'available_slots' => 0]);
    }

    public function incrementViews($userId = null, $ipAddress = null)
    {
        if (class_exists(MarketplaceProductView::class)) {
            MarketplaceProductView::firstOrCreate(
                ['product_id' => $this->id, 'user_id' => $userId, 'ip_address' => $ipAddress],
                ['viewed_at' => now(), 'user_agent' => request()->userAgent()]
            );
        }
        $this->increment('views_count');
    }
}