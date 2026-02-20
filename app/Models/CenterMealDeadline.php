<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CenterMealDeadline extends Model
{
    protected $table = 'center_meal_deadlines';

    protected $fillable = [
        'center_id',
        'meal_type',
        'reservation_from_hour',
        'reservation_to_hour',
        'is_active',
    ];

    protected $casts = [
        'is_active'             => 'boolean',
        'reservation_from_hour' => 'integer',
        'reservation_to_hour'   => 'integer',
        // اگر در آینده بخواهید created_at/updated_at را به عنوان Carbon بخوانید
        // 'created_at'         => 'datetime',
        // 'updated_at'         => 'datetime',
    ];

    /**
     * رابطه با مرکز
     */
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    /**
     * آیا زمان فعلی داخل بازه مجاز رزرو است؟
     */
    public function isWithinDeadline(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $nowHour = now()->hour;

        return $nowHour >= $this->reservation_from_hour &&
               $nowHour <= $this->reservation_to_hour;
    }

    /**
     * متن خوانا برای نمایش بازه زمانی
     *
     * @return string
     */
    public function getDeadlineRangeTextAttribute(): string
    {
        return sprintf(
            "از %02d:00 تا %02d:00",
            $this->reservation_from_hour,
            $this->reservation_to_hour
        );
    }

    /**
     * Scope برای گرفتن فقط رکوردهای فعال
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}