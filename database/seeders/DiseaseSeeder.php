<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Disease;

class DiseaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $diseases = [
            [
                'name' => 'Malaria',
                'description' => 'Mosquito-borne infectious disease affecting humans and other animals',
                'category' => 'infectious'
            ],
            [
                'name' => 'Typhoid',
                'description' => 'Bacterial infection due to Salmonella typhi',
                'category' => 'infectious'
            ],
            [
                'name' => 'Cholera',
                'description' => 'Infection of the small intestine by some strains of the bacterium Vibrio cholerae',
                'category' => 'infectious'
            ],
            [
                'name' => 'Dengue',
                'description' => 'Mosquito-borne tropical disease caused by the dengue virus',
                'category' => 'infectious'
            ],
            [
                'name' => 'Influenza',
                'description' => 'Common viral infection that can be deadly, especially in high-risk groups',
                'category' => 'infectious'
            ],
            [
                'name' => 'Tuberculosis',
                'description' => 'Bacterial infection that mainly affects the lungs',
                'category' => 'infectious'
            ],
            [
                'name' => 'Pneumonia',
                'description' => 'Infection that inflames air sacs in one or both lungs',
                'category' => 'infectious'
            ],
            [
                'name' => 'Diabetes',
                'description' => 'Group of metabolic disorders characterized by high blood sugar',
                'category' => 'chronic'
            ],
            [
                'name' => 'Hypertension',
                'description' => 'Long-term medical condition in which blood pressure is persistently elevated',
                'category' => 'chronic'
            ],
            [
                'name' => 'Asthma',
                'description' => 'Respiratory condition marked by attacks of spasm in the bronchi',
                'category' => 'chronic'
            ],
            [
                'name' => 'Heart Disease',
                'description' => 'Range of conditions that affect the heart',
                'category' => 'chronic'
            ],
            [
                'name' => 'Diarrhea',
                'description' => 'Condition characterized by loose, watery stools',
                'category' => 'infectious'
            ]
        ];

        foreach ($diseases as $disease) {
            Disease::create($disease);
        }
    }
}
