<?php

namespace Database\Seeders;

use App\Models\Center;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CenterSeeder extends Seeder
{
    public function run()
    {
        $centers = [
            [
                'name' => 'بیمارستان امام حسین (ع)',
                'address' => 'طلاب - خیابان وحید (دریا)',
                'description' => 'بیمارستان',
            ],
            [
                'name' => 'درمانگاه ولیعصر (عج)',
                'address' => 'بلوار وکیل آباد - نبش وکیل آباد 65',
                'description' => 'درمانگاه شهری',
            ],
            [
                'name' => 'درمانگاه امام حسین (ع)',
                'address' => 'خیابان امام رضا (ع) - فلکه ضد',
                'description' => 'درمانگاه شهری',
            ],
            [
                'name' => 'درمانگاه اصحاب الحسین',
                'address' => 'قاسم آباد',
                'description' => 'درمانگاه شهری',
            ],
        ];

        foreach ($centers as $center) {
            Center::create($center);
        }
    }
}
