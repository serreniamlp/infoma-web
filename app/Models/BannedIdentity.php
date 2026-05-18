<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedIdentity extends Model
{
    protected $fillable = [
        'type',       // 'email' | 'nik'
        'value',
        'reason',
        'banned_by',
        'user_id',
    ];

    public function bannedBy()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cek apakah email tertentu ada di blacklist.
     */
    public static function isEmailBanned(string $email): bool
    {
        return self::where('type', 'email')
            ->where('value', strtolower(trim($email)))
            ->exists();
    }

    /**
     * Cek apakah NIK tertentu ada di blacklist.
     */
    public static function isNikBanned(string $nik): bool
    {
        return self::where('type', 'nik')
            ->where('value', trim($nik))
            ->exists();
    }

    /**
     * Tambah email ke blacklist.
     */
    public static function banEmail(string $email, string $reason, int $adminId, ?int $userId = null): self
    {
        return self::firstOrCreate(
            ['type' => 'email', 'value' => strtolower(trim($email))],
            ['reason' => $reason, 'banned_by' => $adminId, 'user_id' => $userId]
        );
    }

    /**
     * Tambah NIK ke blacklist.
     */
    public static function banNik(string $nik, string $reason, int $adminId, ?int $userId = null): self
    {
        return self::firstOrCreate(
            ['type' => 'nik', 'value' => trim($nik)],
            ['reason' => $reason, 'banned_by' => $adminId, 'user_id' => $userId]
        );
    }
}