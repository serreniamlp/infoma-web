<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'profile_picture',
        'is_seller',
        'seller_status',
        'seller_ktp',
        'seller_nik',
        'seller_selfie',
        'seller_rejection_reason',
        'provider_status',
        'provider_nik',
        'provider_ktp',
        'provider_selfie',
        'provider_rejection_reason',
        'is_active',
        // ── BAN & TERMS (baru) ──────────────────────────────────────
        'terms_accepted_at',
        'ban_type',
        'banned_until',
        'ban_reason',
        'banned_by',
        'banned_at',
        'pending_role',
        'last_seen_at',
        'fcm_token',     // ← Firebase Cloud Messaging device token
    ];

    protected $casts = [
        'is_seller'         => 'boolean',
        'is_active'         => 'boolean',
        'terms_accepted_at' => 'datetime',
        'banned_until'      => 'datetime',
        'banned_at'         => 'datetime',
        'last_seen_at'      => 'datetime',  // ← BARU
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ── RELATIONS ────────────────────────────────────────────────────────

    public function roles() {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function residences() {
        return $this->hasMany(Residence::class, 'provider_id');
    }

    public function activities() {
        return $this->hasMany(Activity::class, 'provider_id');
    }

    public function bookings() {
        return $this->hasMany(Booking::class);
    }

    public function transactions() {
        return $this->hasManyThrough(Transaction::class, Booking::class);
    }

    public function ratings() {
        return $this->hasMany(Rating::class);
    }

    public function bookmarks() {
        return $this->hasMany(Bookmark::class);
    }

    public function addresses()
    {
        return $this->hasMany(\App\Models\UserAddress::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(\App\Models\UserAddress::class)->where('is_default', true);
    }

    public function marketplaceProducts() {
        return $this->hasMany(MarketplaceProduct::class, 'seller_id');
    }

    public function marketplaceTransactionsAsBuyer() {
        return $this->hasMany(MarketplaceTransaction::class, 'buyer_id');
    }

    public function marketplaceTransactionsAsSeller() {
        return $this->hasMany(MarketplaceTransaction::class, 'seller_id');
    }

    public function marketplaceTransactions() {
        return $this->hasMany(MarketplaceTransaction::class, 'buyer_id');
    }

    public function bannedByAdmin() {
        return $this->belongsTo(User::class, 'banned_by');
    }

    // ── ROLE METHODS ─────────────────────────────────────────────────────

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasAnyRole($roles)
    {
        return $this->roles()->whereIn('name', $roles)->count() > 0;
    }

    public function hasAllRoles($roles)
    {
        return $this->roles()->whereIn('name', $roles)->count() === count($roles);
    }

    public function isSeller(): bool
    {
        return (bool) $this->is_seller;
    }

    /**
     * User dianggap "Online" jika aktif dalam 5 menit terakhir.
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5));
    }

    /**
     * Label status untuk ditampilkan di halaman detail hunian/event/produk.
     * Contoh: "Online", "Terakhir online 2 jam yang lalu", "Belum pernah online".
     */
    public function getLastSeenLabel(): string
    {
        if ($this->isOnline()) {
            return 'Online';
        }

        if (! $this->last_seen_at) {
            return 'Belum pernah online';
        }

        return 'Terakhir online ' . $this->last_seen_at->diffForHumans();
    }

    public function isActive(): bool
    {
        return (bool) ($this->is_active ?? true);
    }

    public function isPendingSeller(): bool
    {
        return $this->seller_status === 'pending';
    }

    public function isApprovedSeller(): bool
    {
        return $this->seller_status === 'approved';
    }

    public function isPendingProvider(): bool
    {
        return $this->provider_status === 'pending';
    }

    public function isApprovedProvider(): bool
    {
        return $this->provider_status === 'approved';
    }

    // ── BAN METHODS (BARU) ───────────────────────────────────────────────

    /**
     * Apakah user saat ini sedang dalam status ban (temporary atau permanent)?
     * Temporary: cek apakah banned_until masih di masa depan.
     * Permanent: selalu true selama ban_type = 'permanent'.
     */
    public function isBanned(): bool
    {
        if ($this->ban_type === 'permanent') {
            return true;
        }

        if ($this->ban_type === 'temporary' && $this->banned_until !== null) {
            return $this->banned_until->isFuture();
        }

        return false;
    }

    /**
     * Apakah user di-ban permanen?
     */
    public function isBannedPermanently(): bool
    {
        return $this->ban_type === 'permanent';
    }

    /**
     * Apakah user di-ban sementara dan masih aktif?
     */
    public function isBannedTemporarily(): bool
    {
        return $this->ban_type === 'temporary'
            && $this->banned_until !== null
            && $this->banned_until->isFuture();
    }

    /**
     * Sisa waktu ban dalam format human-readable.
     * Contoh: "2 hari lagi", "3 jam lagi"
     */
    public function banTimeRemaining(): string
    {
        if ($this->isBannedPermanently()) {
            return 'Permanen';
        }

        if ($this->isBannedTemporarily()) {
            return $this->banned_until->diffForHumans(now(), [
                'parts'  => 2,
                'join'   => true,
                'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW,
            ]);
        }

        return '-';
    }

    /**
     * Apakah user sudah menyetujui S&K verifikasi?
     */
    public function hasAcceptedTerms(): bool
    {
        return $this->terms_accepted_at !== null;
    }

    // ── PROVIDER RELATIONSHIPS ───────────────────────────────────────────

    public function providedResidences()
    {
        return $this->hasMany(Residence::class, 'provider_id');
    }

    public function providedActivities()
    {
        return $this->hasMany(Activity::class, 'provider_id');
    }
}