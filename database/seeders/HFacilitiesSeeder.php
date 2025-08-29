<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HFacilitiesSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            ['name' => 'Al Rahma Hospital'],
            ['name' => 'Tasakhtaa Global Hospital'],
            ['name' => 'Mnazi Mmoja Hospital'],
            ['name' => 'Dr. Mehta’s Hospital'],
            ['name' => 'Tawakal Hospital'],
            ['name' => 'Makunduchi Health Center'],
            ['name' => 'Kivunge Hospital'],
            ['name' => 'Chake Chake Health Center'],
            ['name' => 'Wete Hospital'],
            ['name' => 'Micheweni Health Center'],
            ['name' => 'Amaan Urban Health'],
            ['name' => 'Kisauni Clinic'],
        ];

        foreach ($facilities as $f) {
            DB::table('hfacilities')->updateOrInsert(
                ['name' => $f['name']],
                ['legal_form' => null, 'level' => null, 'qpi_id' => null]
            );
        }
    }
}

