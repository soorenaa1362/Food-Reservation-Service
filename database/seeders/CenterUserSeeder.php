<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CenterUserSeeder extends Seeder
{
    public function run()
    {
        $centerUsers = [
            ['center_id' => 1, 'user_id' => 1], // کاربر 1 به بیمارستان امام حسین
            ['center_id' => 1, 'user_id' => 2], // کاربر 2 به بیمارستان امام حسین
            ['center_id' => 2, 'user_id' => 1], // کاربر 1 به درمانگاه ولیعصر
            ['center_id' => 2, 'user_id' => 3], // کاربر 3 به درمانگاه ولیعصر
            ['center_id' => 1, 'user_id' => 3], // کاربر 3 به بیمارستان امام حسین
            ['center_id' => 3, 'user_id' => 4], // کاربر 4 به درمانگاه امام حسین
            ['center_id' => 3, 'user_id' => 5], // کاربر 5 به درمانگاه امام حسین
            ['center_id' => 4, 'user_id' => 5], // کاربر 5 به درمانگاه اصحاب الحسین
            ['center_id' => 4, 'user_id' => 6], // کاربر 6 به درمانگاه اصحاب الحسین
        ];

        foreach ($centerUsers as $centerUser) {
            DB::table('center_user')->insert($centerUser);
        }
    }
}