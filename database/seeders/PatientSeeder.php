<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\District;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = District::all();
        $genders = ['male', 'female'];
        $economicStatuses = ['low', 'middle', 'high'];

        // Create 1000 sample patients
        for ($i = 0; $i < 1000; $i++) {
            $age = rand(1, 80);

            Patient::create([
                'gender' => $genders[array_rand($genders)],
                'age' => $age,
                'economic_status' => $economicStatuses[array_rand($economicStatuses)],
                'district_id' => $districts->random()->id,
            ]);
        }
    }
}
