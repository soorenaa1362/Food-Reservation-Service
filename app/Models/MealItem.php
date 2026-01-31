<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealItem extends Model
{
    protected $fillable = [
        'meal_id',
        'meal_type',
        'food_name',
        'portions',
        'price',
        'reserved_count',
    ];

    protected $casts = [
        'price' => 'integer',
        'portions' => 'integer',
        'reserved_count' => 'integer',
    ];

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    // موجودی باقی‌مانده (خیلی مهم برای نمایش و رزرو)
    public function getAvailablePortionsAttribute(): int
    {
        return $this->portions - $this->reserved_count;
    }

    // آیا این آیتم قابل رزرو است؟
    public function getIsReservableAttribute(): bool
    {
        return $this->available_portions > 0;
    }

    public function scopeReservable($query)
    {
        return $query->whereColumn('portions', '>', 'reserved_count');
    }
}