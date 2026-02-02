<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    const STATUS_PENDING   = 0;
    const STATUS_SUCCESS   = 1;
    const STATUS_FAILED    = 2;
    const STATUS_CANCELLED = 3;

    const TYPE_CREDIT_CHARGE = 1;
    const TYPE_FOOD_RESERVE  = 2;

    protected $fillable = [
        'user_id',
        'center_id',
        'type',
        'amount',
        'gateway',
        'authority',
        'ref_id',
        'status',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta'   => 'array',
        'amount' => 'integer', // کافیه، چون bigInteger هم به integer cast می‌شه
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    // متدهای کمکی برای وضعیت
    public function isPending(): bool   { return $this->status == self::STATUS_PENDING; }
    public function isSuccess(): bool   { return $this->status == self::STATUS_SUCCESS; }
    public function isFailed(): bool    { return $this->status == self::STATUS_FAILED; }
    public function isCancelled(): bool { return $this->status == self::STATUS_CANCELLED; }

    // متدهای کمکی برای نوع
    public function isCreditCharge(): bool { return $this->type == self::TYPE_CREDIT_CHARGE; }
    public function isFoodReserve(): bool  { return $this->type == self::TYPE_FOOD_RESERVE; }

    // Accessor برای متن نوع تراکنش
    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CREDIT_CHARGE => 'افزایش اعتبار',
            self::TYPE_FOOD_RESERVE  => 'رزرو غذا',
            default => 'نامشخص',
        };
    }

    // Accessor برای کلاس CSS نوع تراکنش
    public function getTypeClassAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CREDIT_CHARGE => 'primary',
            self::TYPE_FOOD_RESERVE  => 'info',
            default => 'secondary',
        };
    }
    
    public function markAsSuccess(string $refId = null): void
    {
        $this->update([
            'status' => self::STATUS_SUCCESS,
            'ref_id' => $refId ?? $this->ref_id,
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }
}