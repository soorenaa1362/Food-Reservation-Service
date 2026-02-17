<?php

namespace App\Models;

use App\Models\Center;
use App\Models\CreditCard;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditLedger extends Model
{
    /**
     * Ledger is immutable → we manage timestamps manually
     */
    public $timestamps = false;

    protected $table = 'credit_ledgers';

    /* ======================================================
     | Constants
     * ====================================================== */

    // Ledger direction
    public const TYPE_INCREASE = 1;
    public const TYPE_DECREASE = 2;

    // Business source
    public const SOURCE_PAYMENT     = 1;
    public const SOURCE_RESERVATION = 2;
    public const SOURCE_MANUAL      = 3;
    public const SOURCE_ADJUSTMENT  = 4;

    // Origin (sync direction)
    public const ORIGIN_LOCAL  = 1;
    public const ORIGIN_HIS    = 2;
    public const ORIGIN_SYSTEM = 3;

    /* ======================================================
     | Mass assignment
     * ====================================================== */

    protected $fillable = [
        'transaction_id',
        'user_id',
        'center_id',
        'credit_card_id',

        'amount',
        'balance_before',
        'balance_after',

        'type',
        'source_type',
        'source_id',

        'origin',
        'external_id',

        'received_from_his_at',
        'sent_to_his_at',

        'description',
        'meta',

        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'received_from_his_at' => 'datetime',
        'sent_to_his_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /* ======================================================
     | Relationships
     * ====================================================== */

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    /* ======================================================
     | Scopes
     * ====================================================== */

    public function scopeInboundFromHis($query)
    {
        return $query->where('origin', self::ORIGIN_HIS);
    }

    public function scopeOutboundToHis($query)
    {
        return $query
            ->where('origin', self::ORIGIN_LOCAL)
            ->whereNull('sent_to_his_at');
    }

    public function scopeForCard($query, int $cardId)
    {
        return $query->where('credit_card_id', $cardId);
    }

    /* ======================================================
     | Helpers
     * ====================================================== */

    public function isIncrease(): bool
    {
        return $this->type === self::TYPE_INCREASE;
    }
    public function getTypeLabel(): string
    {
        return $this->isIncrease() ? 'افزایش موجودی' : 'کاهش موجودی';
    }
    public function getTypeBadgeClass(): string
    {
        return $this->isIncrease() ? 'success' : 'danger';
    }

    public function getSourceLabel(): string
    {
        return match ($this->source_type) {
            self::SOURCE_PAYMENT => 'پرداخت',
            self::SOURCE_RESERVATION => 'رزرو',
            self::SOURCE_MANUAL => 'دستی',
            self::SOURCE_ADJUSTMENT => 'اصلاح',
        };
    }
    public function getSourceBadgeClass(): string
    {
        return match ($this->source_type) {
            self::SOURCE_PAYMENT => 'success',
            self::SOURCE_RESERVATION => 'info',
            self::SOURCE_MANUAL => 'warning',
            self::SOURCE_ADJUSTMENT => 'primary',
            default => 'secondary',
        };
    }


    public function markSentToHis(): void
    {
        $this->forceFill([
            'sent_to_his_at' => now(),
        ])->save();
    }

    /* ======================================================
     | Factory helpers (idempotent create pattern)
     * ====================================================== */

    public static function createIdempotent(array $attributes): self
    {
        if (!empty($attributes['external_id']) && isset($attributes['origin'])) {
            $existing = self::where('origin', $attributes['origin'])
                ->where('external_id', $attributes['external_id'])
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return self::create($attributes);
    }
}
