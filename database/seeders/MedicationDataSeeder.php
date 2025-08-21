<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medication;
use App\Models\Prescription;
use App\Models\Patient;
use Carbon\Carbon;

class MedicationDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏥 Starting comprehensive medication data seeding...');

        // Clear existing data safely
        $this->clearExistingData();

        // Ensure we have comprehensive medications
        $this->seedMedications();

        // Create comprehensive prescription data from 2020 to current
        $this->seedPrescriptions();

        $this->command->info('✅ Medication data seeding completed successfully!');
    }

    /**
     * Clear existing data safely (handle foreign key constraints)
     */
    private function clearExistingData()
    {
        $this->command->info('🗑️ Clearing existing data...');

        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear prescriptions first
        Prescription::truncate();

        // Clear medications
        Medication::truncate();

        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ Existing data cleared');
    }

    /**
     * Seed comprehensive medication data
     */
    private function seedMedications()
    {
        $this->command->info('📋 Seeding medications...');
        
        $medications = [
            // Analgesics (Pain relievers)
            ['name' => 'Paracetamol', 'category' => 'analgesic', 'dosage_form' => 'tablet', 'strength' => '500mg', 'manufacturer' => 'Generic Pharma'],
            ['name' => 'Ibuprofen', 'category' => 'analgesic', 'dosage_form' => 'tablet', 'strength' => '400mg', 'manufacturer' => 'Pain Relief Ltd'],
            ['name' => 'Aspirin', 'category' => 'analgesic', 'dosage_form' => 'tablet', 'strength' => '75mg', 'manufacturer' => 'Cardio Pharma'],
            ['name' => 'Tramadol', 'category' => 'analgesic', 'dosage_form' => 'capsule', 'strength' => '50mg', 'manufacturer' => 'Strong Pain Co'],
            
            // Antibiotics
            ['name' => 'Amoxicillin', 'category' => 'antibiotic', 'dosage_form' => 'capsule', 'strength' => '250mg', 'manufacturer' => 'Antibiotic Solutions'],
            ['name' => 'Azithromycin', 'category' => 'antibiotic', 'dosage_form' => 'tablet', 'strength' => '500mg', 'manufacturer' => 'Infection Control Inc'],
            ['name' => 'Ciprofloxacin', 'category' => 'antibiotic', 'dosage_form' => 'tablet', 'strength' => '500mg', 'manufacturer' => 'Broad Spectrum Ltd'],
            ['name' => 'Doxycycline', 'category' => 'antibiotic', 'dosage_form' => 'capsule', 'strength' => '100mg', 'manufacturer' => 'Tetracycline Co'],
            
            // Antidiabetic
            ['name' => 'Metformin', 'category' => 'antidiabetic', 'dosage_form' => 'tablet', 'strength' => '500mg', 'manufacturer' => 'Diabetes Care Ltd'],
            ['name' => 'Insulin Glargine', 'category' => 'antidiabetic', 'dosage_form' => 'injection', 'strength' => '100IU/ml', 'manufacturer' => 'Insulin Solutions'],
            ['name' => 'Gliclazide', 'category' => 'antidiabetic', 'dosage_form' => 'tablet', 'strength' => '80mg', 'manufacturer' => 'Sugar Control Inc'],
            
            // Antihypertensive
            ['name' => 'Lisinopril', 'category' => 'antihypertensive', 'dosage_form' => 'tablet', 'strength' => '10mg', 'manufacturer' => 'BP Control Ltd'],
            ['name' => 'Amlodipine', 'category' => 'antihypertensive', 'dosage_form' => 'tablet', 'strength' => '5mg', 'manufacturer' => 'Heart Health Co'],
            ['name' => 'Losartan', 'category' => 'antihypertensive', 'dosage_form' => 'tablet', 'strength' => '50mg', 'manufacturer' => 'Pressure Relief Inc'],
            
            // Antihistamine
            ['name' => 'Cetirizine', 'category' => 'antihistamine', 'dosage_form' => 'tablet', 'strength' => '10mg', 'manufacturer' => 'Allergy Solutions'],
            ['name' => 'Loratadine', 'category' => 'antihistamine', 'dosage_form' => 'tablet', 'strength' => '10mg', 'manufacturer' => 'Antihistamine Co'],
            
            // Antacids
            ['name' => 'Omeprazole', 'category' => 'antacid', 'dosage_form' => 'capsule', 'strength' => '20mg', 'manufacturer' => 'Stomach Care Ltd'],
            ['name' => 'Ranitidine', 'category' => 'antacid', 'dosage_form' => 'tablet', 'strength' => '150mg', 'manufacturer' => 'Acid Control Inc'],
            
            // Vitamins
            ['name' => 'Vitamin D3', 'category' => 'vitamin', 'dosage_form' => 'tablet', 'strength' => '1000IU', 'manufacturer' => 'Vitamin Solutions'],
            ['name' => 'Vitamin B Complex', 'category' => 'vitamin', 'dosage_form' => 'tablet', 'strength' => 'Multi', 'manufacturer' => 'Nutrition Co'],
            ['name' => 'Folic Acid', 'category' => 'vitamin', 'dosage_form' => 'tablet', 'strength' => '5mg', 'manufacturer' => 'Prenatal Care Ltd'],
            
            // Hormones
            ['name' => 'Levothyroxine', 'category' => 'hormone', 'dosage_form' => 'tablet', 'strength' => '50mcg', 'manufacturer' => 'Thyroid Solutions'],
            ['name' => 'Prednisolone', 'category' => 'hormone', 'dosage_form' => 'tablet', 'strength' => '5mg', 'manufacturer' => 'Steroid Pharma'],
            
            // Others
            ['name' => 'Salbutamol', 'category' => 'other', 'dosage_form' => 'inhaler', 'strength' => '100mcg', 'manufacturer' => 'Respiratory Care'],
            ['name' => 'Furosemide', 'category' => 'other', 'dosage_form' => 'tablet', 'strength' => '40mg', 'manufacturer' => 'Diuretic Solutions'],
        ];

        foreach ($medications as $medication) {
            Medication::create($medication);
        }

        $this->command->info('✅ Created ' . count($medications) . ' medications');
    }

    /**
     * Seed comprehensive prescription data from 2020 to current
     */
    private function seedPrescriptions()
    {
        $this->command->info('💊 Seeding prescriptions from 2020 to current...');
        
        $medications = Medication::all();
        $patients = Patient::all();
        
        if ($medications->isEmpty() || $patients->isEmpty()) {
            $this->command->error('❌ No medications or patients found. Please seed patients first.');
            return;
        }

        $startDate = Carbon::create(2020, 1, 1);
        $endDate = Carbon::now();
        $totalPrescriptions = 0;

        // Create prescriptions for each year from 2020 to current
        for ($year = 2020; $year <= date('Y'); $year++) {
            $this->command->info("📅 Creating prescriptions for year {$year}...");
            
            $yearStart = Carbon::create($year, 1, 1);
            $yearEnd = Carbon::create($year, 12, 31);
            
            // Ensure each medication gets prescriptions throughout the year
            foreach ($medications as $medication) {
                $this->createMedicationPrescriptions($medication, $patients, $yearStart, $yearEnd);
            }
            
            // Add random additional prescriptions for realistic distribution
            $this->createRandomPrescriptions($medications, $patients, $yearStart, $yearEnd);
        }

        $totalPrescriptions = Prescription::count();
        $this->command->info("✅ Created {$totalPrescriptions} total prescriptions");
        
        // Show distribution by year
        $this->showYearlyDistribution();
    }

    /**
     * Create prescriptions for a specific medication throughout the year
     */
    private function createMedicationPrescriptions($medication, $patients, $yearStart, $yearEnd)
    {
        // Each medication gets 50-200 prescriptions per year
        $prescriptionsPerYear = rand(50, 200);
        
        for ($i = 0; $i < $prescriptionsPerYear; $i++) {
            $patient = $patients->random();
            
            // Create realistic prescription date distribution
            $prescriptionDate = $this->getRealisticDate($yearStart, $yearEnd, $medication);
            
            Prescription::create([
                'medication_id' => $medication->id,
                'patient_id' => $patient->id,
                'prescribed_date' => $prescriptionDate,
                'quantity' => $this->getRealisticQuantity($medication),
                'dosage' => $this->getRealisticDosage($medication),
                'duration_days' => $this->getRealisticDuration($medication),
                'status' => $this->getRealisticStatus($prescriptionDate),
                'prescribed_by' => $this->getRandomDoctor(),
            ]);
        }
    }

    /**
     * Create additional random prescriptions for realistic distribution
     */
    private function createRandomPrescriptions($medications, $patients, $yearStart, $yearEnd)
    {
        // Add 500-1000 additional random prescriptions per year
        $additionalPrescriptions = rand(500, 1000);
        
        for ($i = 0; $i < $additionalPrescriptions; $i++) {
            $medication = $medications->random();
            $patient = $patients->random();
            
            $prescriptionDate = $this->getRealisticDate($yearStart, $yearEnd, $medication);
            
            Prescription::create([
                'medication_id' => $medication->id,
                'patient_id' => $patient->id,
                'prescribed_date' => $prescriptionDate,
                'quantity' => $this->getRealisticQuantity($medication),
                'dosage' => $this->getRealisticDosage($medication),
                'duration_days' => $this->getRealisticDuration($medication),
                'status' => $this->getRealisticStatus($prescriptionDate),
                'prescribed_by' => $this->getRandomDoctor(),
            ]);
        }
    }

    /**
     * Get realistic prescription date with seasonal patterns
     */
    private function getRealisticDate($yearStart, $yearEnd, $medication)
    {
        // Some medications have seasonal patterns
        $seasonalMedications = [
            'Cetirizine' => [3, 4, 5, 9, 10], // Spring and fall allergies
            'Loratadine' => [3, 4, 5, 9, 10],
            'Salbutamol' => [11, 12, 1, 2], // Winter respiratory issues
            'Vitamin D3' => [10, 11, 12, 1, 2], // Winter vitamin deficiency
        ];

        if (isset($seasonalMedications[$medication->name])) {
            $preferredMonths = $seasonalMedications[$medication->name];
            $month = $preferredMonths[array_rand($preferredMonths)];
            $year = $yearStart->year;
            
            return Carbon::create($year, $month, rand(1, 28))
                ->addHours(rand(8, 18))
                ->addMinutes(rand(0, 59));
        }

        // Random date for non-seasonal medications
        return Carbon::createFromTimestamp(
            rand($yearStart->timestamp, $yearEnd->timestamp)
        );
    }

    /**
     * Get realistic quantity based on medication type
     */
    private function getRealisticQuantity($medication)
    {
        switch ($medication->dosage_form) {
            case 'tablet':
            case 'capsule':
                return rand(10, 90); // 10-90 tablets/capsules
            case 'syrup':
                return rand(100, 500); // 100-500ml
            case 'injection':
                return rand(1, 10); // 1-10 vials
            case 'inhaler':
                return rand(1, 3); // 1-3 inhalers
            default:
                return rand(10, 60);
        }
    }

    /**
     * Get realistic dosage based on medication
     */
    private function getRealisticDosage($medication)
    {
        $dosages = [
            'tablet' => ['1 tablet once daily', '1 tablet twice daily', '2 tablets twice daily', '1 tablet three times daily'],
            'capsule' => ['1 capsule once daily', '1 capsule twice daily', '2 capsules daily'],
            'syrup' => ['5ml twice daily', '10ml three times daily', '15ml once daily'],
            'injection' => ['1 injection daily', '1 injection weekly', '1 injection as needed'],
            'inhaler' => ['2 puffs twice daily', '1-2 puffs as needed', '2 puffs four times daily'],
        ];

        $formDosages = $dosages[$medication->dosage_form] ?? ['1 unit as directed'];
        return $formDosages[array_rand($formDosages)];
    }

    /**
     * Get realistic duration based on medication category
     */
    private function getRealisticDuration($medication)
    {
        switch ($medication->category) {
            case 'antibiotic':
                return rand(5, 14); // 5-14 days
            case 'analgesic':
                return rand(3, 10); // 3-10 days
            case 'antidiabetic':
            case 'antihypertensive':
            case 'hormone':
                return rand(30, 90); // 30-90 days (chronic conditions)
            case 'vitamin':
                return rand(30, 180); // 30-180 days
            default:
                return rand(7, 30); // 7-30 days
        }
    }

    /**
     * Get realistic status based on prescription date
     */
    private function getRealisticStatus($prescriptionDate)
    {
        $daysSince = Carbon::now()->diffInDays($prescriptionDate);
        
        if ($daysSince > 90) {
            return 'completed'; // Old prescriptions are completed
        } elseif ($daysSince > 30) {
            return rand(0, 1) ? 'completed' : 'discontinued';
        } else {
            return 'active'; // Recent prescriptions are active
        }
    }

    /**
     * Get random doctor name
     */
    private function getRandomDoctor()
    {
        $doctors = [
            'Dr. Amani Hassan', 'Dr. Fatma Ali', 'Dr. Omar Khamis', 'Dr. Zainab Saleh',
            'Dr. Rashid Mwalimu', 'Dr. Mwajuma Said', 'Dr. Hamad Juma', 'Dr. Asha Mwamba',
            'Dr. Salim Bakari', 'Dr. Halima Othman', 'Dr. Mwanajuma Vuai', 'Dr. Seif Hamad'
        ];
        
        return $doctors[array_rand($doctors)];
    }

    /**
     * Show yearly distribution of prescriptions
     */
    private function showYearlyDistribution()
    {
        $this->command->info('📊 Prescription distribution by year:');
        
        $yearlyData = Prescription::selectRaw('YEAR(prescribed_date) as year, COUNT(*) as count')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        foreach ($yearlyData as $data) {
            $this->command->info("   {$data->year}: {$data->count} prescriptions");
        }
    }
}
