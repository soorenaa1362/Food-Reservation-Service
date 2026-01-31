<?php

namespace App\Repositories\CreditCard;

use App\Models\CreditCard;
use App\Models\Reservation;
use Illuminate\Http\Request;

interface CreditCardRepositoryInterface
{
    public function findCreditCard(int $userId, int $centerId): ?CreditCard;
    public function checkAndDeductCredit(CreditCard $card, float $totalAmount): bool;
    public function storeReservation(Request $request, int $userId, int $centerId, float $totalAmount): Reservation;
    public function saveReservationToJson(int $centerId, Reservation $reservation): bool;
}
