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
'sold_at'
];

protected $casts = [
'price' => 'decimal:2',
'latitude' => 'decimal:8',
'longitude' => 'decimal:8',
'images' => 'array',
'tags' => 'array',
'sold_at' => 'datetime'
];

protected $appends = [
'condition_label',
'status_label',
'main_image',
'rating_average',
'is_available',
'discount_percentage'
];

/**
* Relationship with seller (User).
*/
public function seller(): BelongsTo
{
return $this->belongsTo(User::class, 'seller_id');
}

/**
* Relationship with category.
*/
public function category(): BelongsTo
{
return $this->belongsTo(ProductCategory::class, 'category_id');
}

/**
* Relationship with transactions.
*/
public function transactions(): HasMany
{
return $this->hasMany(MarketplaceTransaction::class, 'product_id');
}

/**
* Polymorphic relationship with bookmarks.
*/
public function bookmarks(): MorphMany
{
return $this->morphMany(Bookmark::class, 'bookmarkable');
}

/**
* Polymorphic relationship with ratings.
*/
public function ratings(): MorphMany
{
return $this->morphMany(Rating::class, 'rateable');
}

/**
* Get product views.
*/
public function views(): HasMany
{
return $this->hasMany(MarketplaceProductView::class, 'product_id');
}

/**
* Scope for active products.
*/
public function scopeActive($query)
{
return $query->where('status', 'active');
}

/**
* Scope for available products.
*/
public function scopeAvailable($query)
{
return $query->where('status', 'active')
->where('stock_quantity', '>', 0);
}

/**
* Scope for products by seller.
*/
public function scopeBySeller($query, $sellerId)
{
return $query->where('seller_id', $sellerId);
}

/**
* Scope for search functionality.
*/
public function scopeSearch($query, $search)
{
return $query->where(function($q) use ($search) {
$q->where('name', 'like', '%' . $search . '%')
->orWhere('description', 'like', '%' . $search . '%')
->orWhereJsonContains('tags', $search);
});
}

/**
* Scope for filtering by condition.
*/
public function scopeByCondition($query, $condition)
{
return $query->where('condition', $condition);
}

/**
* Scope for filtering by price range.
*/
public function scopePriceRange($query, $minPrice = null, $maxPrice = null)
{
if ($minPrice !== null) {
$query->where('price', '>=', $minPrice);
}

if ($maxPrice !== null) {
$query->where('price', '<=', $maxPrice); } return $query; } /** * Scope for filtering by location. */ public function
    scopeByLocation($query, $location) { return $query->where('location', 'like', '%' . $location . '%');
    }

    /**
    * Get condition label.
    */
    public function getConditionLabelAttribute()
    {
    $conditions = [
    'new' => 'Baru',
    'like_new' => 'Seperti Baru',
    'good' => 'Baik',
    'fair' => 'Cukup',
    'needs_repair' => 'Perlu Perbaikan'
    ];

    return $conditions[$this->condition] ?? $this->condition;
    }

    /**
    * Get status label.
    */
    public function getStatusLabelAttribute()
    {
    $statuses = [
    'draft' => 'Draft',
    'active' => 'Aktif',
    'sold' => 'Terjual',
    'inactive' => 'Tidak Aktif',
    'pending_approval' => 'Menunggu Persetujuan'
    ];

    return $statuses[$this->status] ?? $this->status;
    }

    /**
    * Get main image URL.
    */
    public function getMainImageAttribute()
    {
    if (!empty($this->images)) {
    return asset('storage/' . $this->images[0]);
    }

    return asset('images/no-image.png');
    }

    /**
    * Get all image URLs.
    */
    public function getImageUrlsAttribute()
    {
    if (!empty($this->images)) {
    return collect($this->images)->map(function($image) {
    return asset('storage/' . $image);
    })->toArray();
    }

    return [asset('images/no-image.png')];
    }

    /**
    * Get average rating.
    */
    public function getRatingAverageAttribute()
    {
    return $this->ratings()->avg('rating') ?? 0;
    }

    /**
    * Get total ratings count.
    */
    public function getRatingsCountAttribute()
    {
    return $this->ratings()->count();
    }

    /**
    * Check if product is available.
    */
    public function getIsAvailableAttribute()
    {
    return $this->status === 'active' && $this->stock_quantity > 0;
    }

    /**
    * Get discount percentage.
    */
    public function getDiscountPercentageAttribute()
    {
    if ($this->original_price && $this->original_price > $this->price) {
    return round((($this->original_price - $this->price) / $this->original_price) * 100);
    }

    return 0;
    }

    /**
    * Check if user has bookmarked this product.
    */
    public function isBookmarkedBy($userId)
    {
    return $this->bookmarks()->where('user_id', $userId)->exists();
    }

    /**
    * Check if user has bought this product.
    */
    public function isBoughtBy($userId)
    {
    return $this->transactions()
    ->where('buyer_id', $userId)
    ->where('status', 'completed')
    ->exists();
    }

    /**
    * Get sold quantity.
    */
    public function getSoldQuantityAttribute()
    {
    return $this->transactions()
    ->where('status', 'completed')
    ->sum('quantity');
    }

    /**
    * Mark product as sold.
    */
    public function markAsSold()
    {
    $this->update([
    'status' => 'sold',
    'sold_at' => now(),
    'stock_quantity' => 0,
    'available_slots' => 0
    ]);
    }

    /**
    * Increment views count with duplicate prevention.
    */
    public function incrementViews($userId = null, $ipAddress = null)
    {
    // Create view record for analytics
    if (class_exists(MarketplaceProductView::class)) {
    MarketplaceProductView::firstOrCreate([
    'product_id' => $this->id,
    'user_id' => $userId,
    'ip_address' => $ipAddress,
    ], [
    'viewed_at' => now(),
    'user_agent' => request()->userAgent()
    ]);
    }

    $this->increment('views_count');
    }
    }
