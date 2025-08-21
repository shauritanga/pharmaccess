<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index() {
        return view('frontend.index');
    }

    /**
     * Get dashboard analytics data for specified time period
     */
    public function getDashboardData(Request $request)
    {
        try {
            $period = $request->input('period', '1m'); // Default to 1 month

            // Calculate date range based on period
            $dateRange = $this->calculateDateRange($period);

            // Get dashboard metrics
            $data = [
                'metrics' => $this->getDashboardMetrics($dateRange),
                'charts' => $this->getDashboardCharts($dateRange),
                'period' => $period,
                'date_range' => $dateRange
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate date range based on period
     */
    private function calculateDateRange($period)
    {
        $endDate = now();

        switch ($period) {
            case 'today':
                $startDate = now()->startOfDay();
                break;
            case '7d':
                $startDate = now()->subDays(7);
                break;
            case '2w':
                $startDate = now()->subWeeks(2);
                break;
            case '1m':
                $startDate = now()->subMonth();
                break;
            case '3m':
                $startDate = now()->subMonths(3);
                break;
            case '6m':
                $startDate = now()->subMonths(6);
                break;
            case '1y':
                $startDate = now()->subYear();
                break;
            default:
                $startDate = now()->subMonth();
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
            'period' => $period
        ];
    }

    /**
     * Get dashboard metrics for the specified date range
     */
    private function getDashboardMetrics($dateRange)
    {
        // Total patients (unique patients with activity in date range)
        $totalPatients = \App\Models\Patient::whereHas('diseaseCases', function($query) use ($dateRange) {
            $query->whereBetween('reported_date', [$dateRange['start'], $dateRange['end']]);
        })->orWhereHas('prescriptions', function($query) use ($dateRange) {
            $query->whereBetween('prescribed_date', [$dateRange['start'], $dateRange['end']]);
        })->distinct()->count();

        // Total health facilities (static count)
        $totalFacilities = \App\Models\District::count(); // Using districts as proxy for facilities

        // Total prescriptions in date range
        $totalPrescriptions = \App\Models\Prescription::whereBetween('prescribed_date', [$dateRange['start'], $dateRange['end']])->count();

        // Total disease cases in date range
        $totalDiseaseCases = \App\Models\DiseaseCase::whereBetween('reported_date', [$dateRange['start'], $dateRange['end']])->count();

        // Calculate percentage changes (compared to previous period)
        $previousRange = $this->getPreviousPeriodRange($dateRange);

        $previousPatients = \App\Models\Patient::whereHas('diseaseCases', function($query) use ($previousRange) {
            $query->whereBetween('reported_date', [$previousRange['start'], $previousRange['end']]);
        })->orWhereHas('prescriptions', function($query) use ($previousRange) {
            $query->whereBetween('prescribed_date', [$previousRange['start'], $previousRange['end']]);
        })->distinct()->count();

        $previousPrescriptions = \App\Models\Prescription::whereBetween('prescribed_date', [$previousRange['start'], $previousRange['end']])->count();
        $previousDiseaseCases = \App\Models\DiseaseCase::whereBetween('reported_date', [$previousRange['start'], $previousRange['end']])->count();

        return [
            'total_patients' => [
                'value' => number_format($totalPatients),
                'change' => $this->calculatePercentageChange($totalPatients, $previousPatients),
                'trend' => $totalPatients >= $previousPatients ? 'up' : 'down'
            ],
            'health_facilities' => [
                'value' => number_format($totalFacilities),
                'change' => '+0%', // Static for facilities
                'trend' => 'stable'
            ],
            'prescriptions' => [
                'value' => number_format($totalPrescriptions),
                'change' => $this->calculatePercentageChange($totalPrescriptions, $previousPrescriptions),
                'trend' => $totalPrescriptions >= $previousPrescriptions ? 'up' : 'down'
            ],
            'disease_cases' => [
                'value' => number_format($totalDiseaseCases),
                'change' => $this->calculatePercentageChange($totalDiseaseCases, $previousDiseaseCases),
                'trend' => $totalDiseaseCases >= $previousDiseaseCases ? 'up' : 'down'
            ]
        ];
    }

    /**
     * Get previous period date range for comparison
     */
    private function getPreviousPeriodRange($currentRange)
    {
        $periodLength = $currentRange['start']->diffInDays($currentRange['end']);

        return [
            'start' => $currentRange['start']->copy()->subDays($periodLength),
            'end' => $currentRange['start']->copy()->subDay(),
        ];
    }

    /**
     * Calculate percentage change between two values
     */
    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? '+100%' : '0%';
        }

        $change = (($current - $previous) / $previous) * 100;
        $sign = $change >= 0 ? '+' : '';

        return $sign . number_format($change, 1) . '%';
    }

    /**
     * Get dashboard charts data
     */
    private function getDashboardCharts($dateRange)
    {
        return [
            'top_diseases' => $this->getTopDiseases($dateRange),
            'medication_trends' => $this->getMedicationTrends($dateRange),
            'disease_heatmap' => $this->getDiseaseHeatmap($dateRange),
            'chronic_diseases' => $this->getChronicDiseases($dateRange),
            'facility_performance' => $this->getFacilityPerformance($dateRange),
            'age_distribution' => $this->getAgeDistribution($dateRange)
        ];
    }

    /**
     * Get top diseases for the period
     */
    private function getTopDiseases($dateRange)
    {
        $topDiseases = \App\Models\DiseaseCase::with('disease')
            ->whereBetween('reported_date', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('disease_id, COUNT(*) as case_count')
            ->groupBy('disease_id')
            ->orderByDesc('case_count')
            ->limit(5)
            ->get();

        return [
            'labels' => $topDiseases->pluck('disease.name')->toArray(),
            'data' => $topDiseases->pluck('case_count')->toArray()
        ];
    }

    /**
     * Get medication prescription trends
     */
    private function getMedicationTrends($dateRange)
    {
        // Get top 3 medications by prescription count
        $topMedications = \App\Models\Prescription::with('medication')
            ->whereBetween('prescribed_date', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('medication_id, COUNT(*) as prescription_count')
            ->groupBy('medication_id')
            ->orderByDesc('prescription_count')
            ->limit(3)
            ->get();

        // Generate monthly data for each medication
        $monthlyData = [];
        $labels = [];

        // Generate month labels for the period
        $current = $dateRange['start']->copy()->startOfMonth();
        while ($current <= $dateRange['end']) {
            $labels[] = $current->format('M Y');
            $current->addMonth();
        }

        foreach ($topMedications as $medication) {
            $monthlyPrescriptions = [];
            $current = $dateRange['start']->copy()->startOfMonth();

            while ($current <= $dateRange['end']) {
                $count = \App\Models\Prescription::where('medication_id', $medication->medication_id)
                    ->whereYear('prescribed_date', $current->year)
                    ->whereMonth('prescribed_date', $current->month)
                    ->count();

                $monthlyPrescriptions[] = $count;
                $current->addMonth();
            }

            $monthlyData[] = [
                'name' => $medication->medication->name ?? 'Unknown',
                'data' => $monthlyPrescriptions
            ];
        }

        return [
            'labels' => $labels,
            'series' => $monthlyData
        ];
    }

    /**
     * Get disease heatmap data by district and disease type
     */
    private function getDiseaseHeatmap($dateRange)
    {
        // Get top 5 diseases for the heatmap
        $topDiseases = \App\Models\DiseaseCase::with('disease')
            ->whereBetween('reported_date', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('disease_id, COUNT(*) as case_count')
            ->groupBy('disease_id')
            ->orderByDesc('case_count')
            ->limit(5)
            ->get();

        // Get all districts
        $districts = \App\Models\District::orderBy('name')->get();

        // Build heatmap series data
        $heatmapSeries = [];

        foreach ($topDiseases as $diseaseData) {
            $disease = $diseaseData->disease;
            $seriesData = [];

            foreach ($districts as $district) {
                // Count cases for this disease in this district
                $caseCount = \App\Models\DiseaseCase::whereHas('patient', function($query) use ($district) {
                    $query->where('district_id', $district->id);
                })
                ->where('disease_id', $disease->id)
                ->whereBetween('reported_date', [$dateRange['start'], $dateRange['end']])
                ->count();

                $seriesData[] = [
                    'x' => $district->name,
                    'y' => $caseCount
                ];
            }

            $heatmapSeries[] = [
                'name' => $disease->name,
                'data' => $seriesData
            ];
        }

        return [
            'series' => $heatmapSeries,
            'districts' => $districts->pluck('name')->toArray()
        ];
    }

    /**
     * Get patient age distribution data
     */
    private function getAgeDistribution($dateRange)
    {
        // Get patients with activity in the date range
        $patients = \App\Models\Patient::whereHas('diseaseCases', function($query) use ($dateRange) {
            $query->whereBetween('reported_date', [$dateRange['start'], $dateRange['end']]);
        })->orWhereHas('prescriptions', function($query) use ($dateRange) {
            $query->whereBetween('prescribed_date', [$dateRange['start'], $dateRange['end']]);
        })->distinct()->get();

        // Calculate age groups
        $ageGroups = [
            '0-10 years' => 0,
            '11-20 years' => 0,
            '21-30 years' => 0,
            '31-40 years' => 0,
            '41-50 years' => 0,
            '51-60 years' => 0,
            '60+ years' => 0
        ];

        foreach ($patients as $patient) {
            if ($patient->date_of_birth) {
                $age = \Carbon\Carbon::parse($patient->date_of_birth)->age;

                if ($age <= 10) {
                    $ageGroups['0-10 years']++;
                } elseif ($age <= 20) {
                    $ageGroups['11-20 years']++;
                } elseif ($age <= 30) {
                    $ageGroups['21-30 years']++;
                } elseif ($age <= 40) {
                    $ageGroups['31-40 years']++;
                } elseif ($age <= 50) {
                    $ageGroups['41-50 years']++;
                } elseif ($age <= 60) {
                    $ageGroups['51-60 years']++;
                } else {
                    $ageGroups['60+ years']++;
                }
            }
        }

        return [
            'series' => array_values($ageGroups),
            'labels' => array_keys($ageGroups)
        ];
    }

    /**
     * Get chronic diseases data
     */
    private function getChronicDiseases($dateRange)
    {
        // Define chronic diseases (you can adjust this list)
        $chronicDiseaseNames = ['Diabetes', 'Hypertension', 'Asthma', 'Cancer', 'HIV/AIDS', 'Arthritis'];

        $chronicDiseases = \App\Models\DiseaseCase::with('disease')
            ->whereBetween('reported_date', [$dateRange['start'], $dateRange['end']])
            ->whereHas('disease', function($query) use ($chronicDiseaseNames) {
                $query->whereIn('name', $chronicDiseaseNames);
            })
            ->selectRaw('disease_id, COUNT(*) as case_count')
            ->groupBy('disease_id')
            ->orderByDesc('case_count')
            ->get();

        return [
            'labels' => $chronicDiseases->pluck('disease.name')->toArray(),
            'data' => $chronicDiseases->pluck('case_count')->toArray()
        ];
    }

    /**
     * Get facility performance data (simplified)
     */
    private function getFacilityPerformance($dateRange)
    {
        // This is a simplified version - you can enhance based on actual facility data
        $districts = \App\Models\District::with(['patients.diseaseCases' => function($query) use ($dateRange) {
            $query->whereBetween('reported_date', [$dateRange['start'], $dateRange['end']]);
        }])->get();

        $performanceData = $districts->map(function($district, $index) {
            $totalCases = $district->patients->sum(function($patient) {
                return $patient->diseaseCases->count();
            });

            // Simulate quality score (1-5) and outcome percentage
            $qualityScore = rand(2, 5);
            $outcomePercentage = rand(60, 95);

            return [
                'facility' => $district->name,
                'quality' => $qualityScore,
                'outcome' => $outcomePercentage,
                'cases' => $totalCases
            ];
        })->filter(function($item) {
            return $item['cases'] > 0;
        })->take(5);

        return $performanceData->values();
    }

    public function hospitals() {
        return view('frontend.hospitals');
    }

    public function Addhospitals() {
        return view('frontend.add-hospitals');
    }

    public function Edithospitals() {
        return view('frontend.edit-hospitals');
    }

    public function medication(Request $request)
    {
        try {
            // Get available medications from database (or create sample data if none exist)
            $availableMedications = \App\Models\Medication::select('id', 'name', 'category')
                ->orderBy('name')
                ->get();

            // If no medications exist, create sample data
            if ($availableMedications->isEmpty()) {
                $this->createSampleMedications();
                $availableMedications = \App\Models\Medication::select('id', 'name', 'category')
                    ->orderBy('name')
                    ->get();
            }

            // Get available years from prescriptions (or default range)
            $availableYears = \App\Models\Prescription::selectRaw('DISTINCT YEAR(prescribed_date) as year')
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($availableYears)) {
                $availableYears = range(2020, 2025);
            }

            // Set default years
            $currentYear = date('Y');
            $startYear = !empty($availableYears) ? min($availableYears) : 2020;

            // Get total prescriptions count
            $totalPrescriptions = \App\Models\Prescription::count();

            return view('frontend.medication', compact(
                'availableMedications',
                'availableYears',
                'currentYear',
                'startYear',
                'totalPrescriptions'
            ));

        } catch (\Exception $e) {
            // Fallback to basic view if database isn't set up yet
            $availableMedications = collect([]);
            $availableYears = range(2021, 2025);
            $currentYear = date('Y');
            $startYear = 2021;
            $totalPrescriptions = 0;

            return view('frontend.medication', compact(
                'availableMedications',
                'availableYears',
                'currentYear',
                'startYear',
                'totalPrescriptions'
            ));
        }
    }

    /**
     * Create sample medications for demonstration
     */
    private function createSampleMedications()
    {
        $medications = [
            ['name' => 'Paracetamol', 'category' => 'analgesic', 'dosage_form' => 'tablet', 'strength' => '500mg'],
            ['name' => 'Amoxicillin', 'category' => 'antibiotic', 'dosage_form' => 'capsule', 'strength' => '250mg'],
            ['name' => 'Metformin', 'category' => 'antidiabetic', 'dosage_form' => 'tablet', 'strength' => '500mg'],
            ['name' => 'Lisinopril', 'category' => 'antihypertensive', 'dosage_form' => 'tablet', 'strength' => '10mg'],
            ['name' => 'Cetirizine', 'category' => 'antihistamine', 'dosage_form' => 'tablet', 'strength' => '10mg'],
            ['name' => 'Omeprazole', 'category' => 'antacid', 'dosage_form' => 'capsule', 'strength' => '20mg'],
            ['name' => 'Insulin', 'category' => 'hormone', 'dosage_form' => 'injection', 'strength' => '100IU/ml'],
            ['name' => 'Ibuprofen', 'category' => 'analgesic', 'dosage_form' => 'tablet', 'strength' => '400mg'],
            ['name' => 'Aspirin', 'category' => 'analgesic', 'dosage_form' => 'tablet', 'strength' => '75mg'],
            ['name' => 'Vitamin D', 'category' => 'vitamin', 'dosage_form' => 'tablet', 'strength' => '1000IU'],
        ];

        foreach ($medications as $medication) {
            \App\Models\Medication::create($medication);
        }
    }


    public function topDisease() {
        return view('frontend.top-diseases');
    }

    public function settings() {
        return view('frontend.settings');
    }

    public function patients()
    {
    $patientData = [
        'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        'totals' => [120, 150, 170, 160, 190, 210, 230, 250, 270, 300, 320, 350]
    ];

    return view('frontend.patients', compact('patientData'));
    }


    public function showChronicDiseases()
    {
    $chronicDiseases = [
        ['name' => 'Diabetes', 'cases' => 1250],
        ['name' => 'Hypertension', 'cases' => 980],
        ['name' => 'Asthma', 'cases' => 670],
        ['name' => 'Heart Disease', 'cases' => 520],
        // Add more if needed
    ];

    return view('frontend.chronic-diseases', compact('chronicDiseases'));
    }

    public function showTopDiseases()
    {
        // Get available diseases and years for filters
        $availableDiseases = \App\Models\Disease::select('id', 'name', 'category')
            ->orderBy('name')
            ->get();

        $availableYears = \App\Models\DiseaseCase::selectRaw('YEAR(reported_date) as year')
            ->distinct()
            ->orderBy('year')
            ->pluck('year')
            ->toArray();

        // Get current year for default filter
        $currentYear = date('Y');
        $startYear = !empty($availableYears) ? min($availableYears) : 2020;

        // Get initial data for the table (top diseases by case count)
        $diseases = \App\Models\Disease::withCount(['diseaseCases' => function ($query) {
                $query->whereYear('reported_date', date('Y'));
            }])
            ->orderBy('disease_cases_count', 'desc')
            ->take(10)
            ->get()
            ->map(function ($disease) {
                return [
                    'name' => $disease->name,
                    'cases' => $disease->disease_cases_count
                ];
            })
            ->toArray();

        return view('frontend.top-diseases', compact(
            'availableDiseases',
            'availableYears',
            'currentYear',
            'startYear',
            'diseases'
        ));
    }

    public function showDiseaseDetail($name)
    {
    // You can load data from database instead of this dummy array
    $detail = [
        'name' => ucfirst($name),
        'reported' => rand(100, 200),
        'solved' => rand(50, 100),
        'unsolved' => rand(10, 50),
    ];

    return view('frontend.disease-detail', compact('detail'));
    }



}
