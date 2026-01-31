<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    
    
    // Accessor 
    // دسترسی راحت به نام مرکز
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . $this->type . ')';
    }
}