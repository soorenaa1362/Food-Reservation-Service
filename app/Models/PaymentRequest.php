<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRequest extends Model
{
    protected $fillable = [
        'user_id',
        'center_id',
        'transaction_id',
        'gateway',
        'amount',
        'res_num',
        'token',
        'ref_num',
        'trace_num',
        'status',
        'paid_at',
        'verified_at',
        'settled_at',
        'callback_payload',
        'gateway_response',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'callback_payload' => 'array',
        'gateway_response' => 'array',
        'paid_at'          => 'datetime',
        'verified_at'      => 'datetime',
        'settled_at'       => 'datetime',
        'amount'           => 'integer',
    ];

    /* =========================
     |  Status Constants
     ========================= */

    const STATUS_PENDING    = 0; // ساخته شده
    const STATUS_REDIRECTED = 1; // کاربر رفته درگاه
    const STATUS_PAID       = 2; // بانک گفته پرداخت شده
    const STATUS_VERIFIED   = 3; // Verify موفق
    const STATUS_SETTLED    = 4; // Settle موفق (نهایی)
    const STATUS_FAILED     = 5; // ناموفق

    /* =========================
     |  Relationships
     ========================= */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /* =========================
     |  Status Checkers
     ========================= */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRedirected(): bool
    {
        return $this->status === self::STATUS_REDIRECTED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isSettled(): bool
    {
        return $this->status === self::STATUS_SETTLED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /* =========================
     |  Transition Guards
     ========================= */

    public function canRedirect(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canMarkPaid(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_REDIRECTED,
        ], true);
    }

    public function canVerify(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function canSettle(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    /* =========================
     |  State Mutators
     ========================= */

    public function markRedirected(string $token): void
    {
        if (! $this->canRedirect()) {
            return;
        }

        $this->update([
            'status' => self::STATUS_REDIRECTED,
            'token'  => $token,
        ]);
    }

    public function markPaid(array $callbackPayload): void
    {
        if (! $this->canMarkPaid()) {
            return;
        }

        $this->update([
            'status'           => self::STATUS_PAID,
            'callback_payload' => $callbackPayload,
            'paid_at'          => now(),
        ]);
    }

    public function markVerified(array $response): void
    {
        if (! $this->canVerify()) {
            return;
        }

        $this->update([
            'status'           => self::STATUS_VERIFIED,
            'gateway_response' => $response,
            'verified_at'      => now(),
        ]);
    }

    public function markSettled(array $response): void
    {
        if (! $this->canSettle()) {
            return;
        }

        $this->update([
            'status'           => self::STATUS_SETTLED,
            'gateway_response' => $response,
            'settled_at'       => now(),
        ]);
    }

    public function markFailed(array $data = []): void
    {
        $this->update([
            'status'           => self::STATUS_FAILED,
            'gateway_response' => $data,
        ]);
    }
}
