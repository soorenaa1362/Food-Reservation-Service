<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Center;
use App\Models\CreditCard;
use Illuminate\Database\Seeder;

class CreditCardSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $centers = Center::all();

        foreach ($users as $user) {
            foreach ($centers as $center) {
                CreditCard::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'center_id' => $center->id
                    ],
                    [
                        'balance' => 100000,
                        'reserved_amount' => 0,
                        'available_balance' => 100000
                    ]
                );
            }
        }
    }
}