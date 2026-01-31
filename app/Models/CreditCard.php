<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCard extends Model
{
    protected $fillable = [
        'user_id',
        'center_id',
        'balance',
        // 'initial_credit',
        'membership_type',
        'credit_expires_at',
    ];

    protected $casts = [
        'balance' => 'decimal:0',
        // 'initial_credit' => 'decimal:0',
        'credit_expires_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
}