<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\District;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Read districts data from JSON file
        $jsonPath = base_path('districts.json');

        if (!file_exists($jsonPath)) {
            throw new \Exception('districts.json file not found in the project root');
        }

        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in districts.json file: ' . json_last_error_msg());
        }

        if (!isset($data['districts']) || !is_array($data['districts'])) {
            throw new \Exception('districts.json must contain a "districts" array');
        }

        // Get district names from JSON to know which ones to keep
        $jsonDistrictNames = collect($data['districts'])->pluck('name')->filter()->toArray();

        // Remove old districts that are not in the JSON file
        District::whereNotIn('name', $jsonDistrictNames)->delete();

        // Insert or update districts from JSON file
        foreach ($data['districts'] as $districtData) {
            // Skip if name is empty
            if (empty($districtData['name'])) {
                continue;
            }

            District::updateOrCreate(
                ['name' => $districtData['name']], // Find by name
                [
                    'latitude' => $districtData['latitude'],
                    'longitude' => $districtData['longitude'],
                    'population' => $districtData['population']
                ]
            );
        }

        $this->command->info('Successfully seeded ' . count($data['districts']) . ' districts from districts.json');
    }
}
