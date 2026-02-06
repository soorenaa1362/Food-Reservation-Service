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
        'sent_to_his',      
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'integer',
        'total' => 'integer',
        'sent_to_his' => 'datetime',
    ];

    public function reservation() 
    { 
        return $this->belongsTo(Reservation::class); 
    }
}
