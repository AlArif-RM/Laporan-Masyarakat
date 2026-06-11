<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LaragonDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DistrictAdminSeeder::class,
            DemoReportSeeder::class,
        ]);
    }
}
