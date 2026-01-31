<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'center_id',
        'total_amount',
        'reservation_date',
        'status',
        'reserved_at',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'reserved_at'      => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function center() { return $this->belongsTo(Center::class); }
    public function items() { return $this->hasMany(ReservationItem::class); }
}
