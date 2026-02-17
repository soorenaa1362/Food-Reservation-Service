<?php

namespace App\Models;

use App\Models\Center;
use App\Models\CreditCard;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    protected $fillable = [
        'national_code_hashed',
        'mobile_hashed',
        'encrypted_first_name',
        'encrypted_last_name',
        'encrypted_full_name',
        'otp_code',
        'otp_expires_at',
        'otp_attempts',
        'otp_locked_until',
        'is_active',
    ];

    protected $hidden = [
        'national_code_hashed',
        'mobile_hashed',
        'otp_code',
        'otp_expires_at',
        'otp_attempts',
        'otp_locked_until',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
        'otp_locked_until' => 'datetime',
        'is_active' => 'boolean',
    ];


    // Relations
    public function centers(): BelongsToMany
    {
        return $this->belongsToMany(Center::class, 'center_user')
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    public function creditCards(): HasMany
    {
        return $this->hasMany(CreditCard::class);
    }


    // Accessor
    public function getFullNameAttribute(): ?string
    {
        if ($this->encrypted_full_name) {
            return Crypt::decryptString($this->encrypted_full_name);
        }

        $first = $this->encrypted_first_name ? Crypt::decryptString($this->encrypted_first_name) : null;
        $last = $this->encrypted_last_name ? Crypt::decryptString($this->encrypted_last_name) : null;

        return $first && $last ? $first . ' ' . $last : null;
    }

    public function getFirstNameAttribute(): ?string
    {
        return $this->encrypted_first_name ? Crypt::decryptString($this->encrypted_first_name) : null;
    }

    public function getLastNameAttribute(): ?string
    {
        return $this->encrypted_last_name ? Crypt::decryptString($this->encrypted_last_name) : null;
    }


    // (اختیاری) اکسسور برای گرفتن بالانس یک مرکز خاص
    public function getBalanceForCenter(int $centerId): int
    {
        return $this->creditCards()->where('center_id', $centerId)->value('balance') ?? 0;
    }
}