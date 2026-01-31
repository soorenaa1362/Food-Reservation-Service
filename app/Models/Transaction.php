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

    protected $fillable = [
        'user_id',
        'center_id',
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