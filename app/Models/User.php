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
        'seller_rejection_reason',
        'provider_status',
        'provider_rejection_reason',
        'is_active',
    ];

    protected $casts = [
        'is_seller' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // RELATIONS
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

    // ROLE METHODS
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasAnyRole($roles)
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasAllRoles($roles)
    {
        return $this->roles()->whereIn('name', $roles)->count() === count($roles);
    }

    public function isSeller(): bool
    {
        return (bool) $this->is_seller;
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

    // PROVIDER RELATIONSHIPS
    public function providedResidences()
    {
        return $this->hasMany(Residence::class, 'provider_id');
    }

    public function providedActivities()
    {
        return $this->hasMany(Activity::class, 'provider_id');
    }

    // MARKETPLACE RELATIONSHIPS
    public function marketplaceProducts()
    {
        return $this->hasMany(MarketplaceProduct::class, 'seller_id');
    }

    public function marketplaceTransactionsAsBuyer()
    {
        return $this->hasMany(MarketplaceTransaction::class, 'buyer_id');
    }

    public function marketplaceTransactionsAsSeller()
    {
        return $this->hasMany(MarketplaceTransaction::class, 'seller_id');
    }

    public function marketplaceTransactions()
    {
        return $this->hasMany(MarketplaceTransaction::class, 'buyer_id');
    }
}

// APPROVAL HELPERS - ditambahkan untuk admin panel
// (diinjeksi di atas closing brace terakhir)
