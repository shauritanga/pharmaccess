<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DiseaseCase;
use App\Models\Disease;
use App\Models\Patient;
use Carbon\Carbon;

class DiseaseCaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $diseases = Disease::all();
        $patients = Patient::all();
        $statuses = ['active', 'recovered', 'deceased'];
        $severities = ['mild', 'moderate', 'severe'];

        // Create disease cases from 2020 to current year
        $startDate = Carbon::create(2020, 1, 1);
        $endDate = Carbon::now();

        // Create 5000 sample disease cases
        for ($i = 0; $i < 5000; $i++) {
            $reportedDate = Carbon::createFromTimestamp(
                rand($startDate->timestamp, $endDate->timestamp)
            );

            DiseaseCase::create([
                'disease_id' => $diseases->random()->id,
                'patient_id' => $patients->random()->id,
                'reported_date' => $reportedDate,
                'status' => $statuses[array_rand($statuses)],
                'severity' => $severities[array_rand($severities)],
            ]);
        }
    }
}
