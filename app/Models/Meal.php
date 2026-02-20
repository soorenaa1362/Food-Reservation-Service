<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meal extends Model
{
    protected $fillable = ['center_id', 'date'];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MealItem::class);
    }

    // روابط کمکی برای دسترسی سریع به وعده‌ها
    public function breakfast(): HasMany
    {
        return $this->items()->where('meal_type', 'breakfast');
    }

    public function lunch(): HasMany
    {
        return $this->items()->where('meal_type', 'lunch');
    }

    public function dinner(): HasMany
    {
        return $this->items()->where('meal_type', 'dinner');
    }
}