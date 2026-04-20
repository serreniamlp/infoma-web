<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceProductView extends Model
{
use HasFactory;

protected $fillable = [
'product_id',
'user_id',
'ip_address',
'user_agent',
'viewed_at'
];

protected $casts = [
'viewed_at' => 'datetime'
];

public $timestamps = false; // Using viewed_at instead

/**
* Relationship with product.
*/
public function product(): BelongsTo
{
return $this->belongsTo(MarketplaceProduct::class, 'product_id');
}

/**
* Relationship with user.
*/
public function user(): BelongsTo
{
return $this->belongsTo(User::class, 'user_id');
}

/**
* Boot method for model events.
*/
protected static function boot()
{
parent::boot();

static::creating(function ($view) {
if (empty($view->viewed_at)) {
$view->viewed_at = now();
}
});
}
}