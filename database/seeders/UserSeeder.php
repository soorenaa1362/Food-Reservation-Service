<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    public function run()
    {
        $nationalCodes = [
            '0941086690',
            '5229710081',
            '0922418500',
            '0925051314',
            '0795239068',
            '0925315117',
        ];

        foreach ($nationalCodes as $code) {
            User::create([
                'national_code' => hash('sha256', $code), // هش کردن کد ملی
            ]);
        }
    }
}