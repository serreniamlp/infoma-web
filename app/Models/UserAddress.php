<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'address',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // ── RELATIONS ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── METHODS ──────────────────────────────────────────────────────────

    /**
     * Set alamat ini sebagai default.
     * Otomatis unset default dari alamat lain milik user yang sama.
     */
    public function setAsDefault(): void
    {
        // Unset semua alamat default milik user ini
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Label yang ditampilkan di UI — contoh: "Rumah (Default)"
     */
    public function getDisplayLabelAttribute(): string
    {
        return $this->label . ($this->is_default ? ' (Default)' : '');
    }
}