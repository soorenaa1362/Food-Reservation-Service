<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    /* =========================
     |  Status Constants
     ========================= */
    const STATUS_PENDING   = 0;
    const STATUS_SUCCESS   = 1;
    const STATUS_FAILED    = 2;
    const STATUS_CANCELLED = 3;

    /* =========================
     |  Type Constants
     ========================= */
    const TYPE_CREDIT_CHARGE = 1; // افزایش اعتبار
    const TYPE_FOOD_RESERVE  = 2; // رزرو غذا

    /* =========================
     |  Fillable / Casts
     ========================= */
    protected $fillable = [
        'user_id',
        'center_id',
        'type',
        'amount',
        'status',
        'description',
        'meta',
    ];

    protected $casts = [
        'amount' => 'integer',
        'meta'   => 'array',
    ];

    /* =========================
     |  Relationships
     ========================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    // Ledgerهای مربوط به این تراکنش
    public function ledgers(): HasMany
    {
        return $this->hasMany(CreditLedger::class);
    }

    /* =========================
     |  Status Helpers
     ========================= */
    public function isPending(): bool   { return $this->status == self::STATUS_PENDING; }
    public function isSuccess(): bool   { return $this->status == self::STATUS_SUCCESS; }
    public function isFailed(): bool    { return $this->status == self::STATUS_FAILED; }
    public function isCancelled(): bool { return $this->status == self::STATUS_CANCELLED; }

    /* =========================
     |  State Mutators
     ========================= */
    public function markAsSuccess(): void
    {
        $this->update(['status' => self::STATUS_SUCCESS]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /* =========================
     |  Accessors
     ========================= */
    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CREDIT_CHARGE => 'افزایش اعتبار',
            self::TYPE_FOOD_RESERVE  => 'رزرو غذا',
            default => 'نامشخص',
        };
    }

    public function getTypeClassAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CREDIT_CHARGE => 'primary',
            self::TYPE_FOOD_RESERVE  => 'info',
            default => 'secondary',
        };
    }
}
