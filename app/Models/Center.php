<?php

namespace App\Models;

use App\Models\CenterMealDeadline;
use App\Models\CreditCard;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Center extends Model
{
    protected $fillable = [
        'his_center_id',
        'name',
        'type',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relation  
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'center_user')
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    public function creditCards(): HasMany
    {
        return $this->hasMany(CreditCard::class);
    }

    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class);
    }

    public function mealDeadlines()
    {
        return $this->hasMany(CenterMealDeadline::class);
    }
    
    
    // Accessor 
    // دسترسی راحت به نام مرکز
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . $this->type . ')';
    }
}