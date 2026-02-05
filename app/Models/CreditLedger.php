<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditLedger extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'transaction_id', // ✅ اضافه شد
        'user_id',
        'center_id',
        'credit_card_id',
        'amount',
        'balance_before',
        'balance_after',
        'type',
        'source_type',
        'source_id',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];

    /* =========================
     |  Relationships
     ========================= */

    // ✅ جدید
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

    /* =========================
     |  Constants
     ========================= */

    const TYPE_INCREASE = 1;
    const TYPE_DECREASE = 2;

    const SOURCE_PAYMENT     = 1;
    const SOURCE_RESERVATION = 2;
    const SOURCE_MANUAL      = 3;

    /* =========================
     |  Static Helpers
     ========================= */

    // ⬇️ همون helperهای خودت — دست نخورده
    public static function increase(
        CreditCard $card,
        int $amount,
        int $sourceType,
        ?int $sourceId = null,
        ?string $description = null,
        array $meta = []
    ): self
    {
        $balanceBefore = $card->balance;
        $balanceAfter = $balanceBefore + $amount;

        $ledger = self::create([
            'user_id'        => $card->user_id,
            'center_id'      => $card->center_id,
            'credit_card_id' => $card->id,
            'amount'         => $amount,
            'balance_before' => $balanceBefore,
            'balance_after'  => $balanceAfter,
            'type'           => self::TYPE_INCREASE,
            'source_type'    => $sourceType,
            'source_id'      => $sourceId,
            'description'    => $description,
            'meta'           => $meta,
        ]);

        $card->update(['balance' => $balanceAfter]);

        return $ledger;
    }

    public static function decrease(
        CreditCard $card,
        int $amount,
        int $sourceType,
        ?int $sourceId = null,
        ?string $description = null,
        array $meta = []
    ): self
    {
        $balanceBefore = $card->balance;
        $balanceAfter = $balanceBefore - $amount;

        if ($balanceAfter < 0) {
            throw new \Exception("Insufficient credit");
        }

        $ledger = self::create([
            'user_id'        => $card->user_id,
            'center_id'      => $card->center_id,
            'credit_card_id' => $card->id,
            'amount'         => -$amount,
            'balance_before' => $balanceBefore,
            'balance_after'  => $balanceAfter,
            'type'           => self::TYPE_DECREASE,
            'source_type'    => $sourceType,
            'source_id'      => $sourceId,
            'description'    => $description,
            'meta'           => $meta,
        ]);

        $card->update(['balance' => $balanceAfter]);

        return $ledger;
    }

    /* =========================
     |  Accessors
     ========================= */

    protected $appends = [
        'type_text',
        'type_class',
        'source_type_text',
        'source_type_class'
    ];

    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            self::TYPE_INCREASE => 'افزایش',
            self::TYPE_DECREASE => 'کاهش',
            default => 'نامشخص',
        };
    }

    public function getTypeClassAttribute(): string
    {
        return match($this->type) {
            self::TYPE_INCREASE => 'success',
            self::TYPE_DECREASE => 'danger',
            default => 'secondary',
        };
    }

    public function getSourceTypeTextAttribute(): string
    {
        return match($this->source_type) {
            self::SOURCE_PAYMENT => 'پرداخت',
            self::SOURCE_RESERVATION => 'رزرو غذا',
            self::SOURCE_MANUAL => 'دستی',
            default => 'نامشخص',
        };
    }

    public function getSourceTypeClassAttribute(): string
    {
        return match($this->source_type) {
            self::SOURCE_PAYMENT => 'info',
            self::SOURCE_RESERVATION => 'warning',
            self::SOURCE_MANUAL => 'dark',
            default => 'secondary',
        };
    }
}
