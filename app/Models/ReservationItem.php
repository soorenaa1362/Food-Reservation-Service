<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationItem extends Model
{
    protected $fillable = [
        'reservation_id',
        'meal_item_id',
        'food_name',
        'meal_type',
        'quantity',
        'price',
        'total',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'integer',
        'total' => 'integer',
    ];

    public function reservation() { return $this->belongsTo(Reservation::class); }
}
