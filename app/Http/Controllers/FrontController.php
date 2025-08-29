<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
            $period = $request->input('period', 'this_year'); // Default to this year

            // Calculate date range based on period
            $dateRange = $this->calculateDateRange($period);

            // Cache by period and end date for 5 minutes
            $cacheKey = 'dashboard:'. $period .':'. $dateRange['end']->format('Y-m-d');
            $data = Cache::remember($cacheKey, 300, function () use ($dateRange, $period) {
                return [
                    'metrics' => $this->getDashboardMetrics($dateRange),
                    'charts' => $this->getDashboardCharts($dateRange),
                    'period' => $period,
                    'date_range' => $dateRange
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            Log::error('Dashboard API failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data.'
            ], 200);
        }
    }

    /**
     * Calculate date range based on period
     */
    private function calculateDateRange($period)
    {
        $endDate = now();

        switch ($period) {
            case 'this_year':
                $startDate = now()->startOfYear();
                break;
            case '2y':
                $startDate = now()->copy()->subYears(2)->startOfDay();
                break;
            case '3y':
                $startDate = now()->copy()->subYears(3)->startOfDay();
                break;
            case 'since_2020':
                $startDate = now()->setYear(2020)->startOfYear();
                break;
            default:
                $startDate = now()->startOfYear();
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
            'medication_geo' => $this->getMedicationGeoHeatmap($dateRange),
            'chronic_diseases' => $this->getChronicDiseases($dateRange),
            'facility_performance' => $this->getFacilityPerformance($dateRange),
            'age_distribution' => $this->getAgeDistribution($dateRange)
        ];
    }

    /**
     * Medication usage by Shehia (norm_* tables)
     */
    private function getMedicationGeoHeatmap($dateRange)
    {
        // Aggregate prescriptions by shehia using normalized tables
        $rows = DB::table('norm_meeting_medications as mm')
            ->join('norm_meetings as m', 'm.id', '=', 'mm.meeting_id')
            ->join('norm_patients as p', 'p.id', '=', 'm.patient_id')
            ->join('norm_shehia as sh', 'sh.id', '=', 'p.shehia_id')
            ->whereBetween('m.meeting_date', [$dateRange['start'], $dateRange['end']])
            ->select('sh.name as shehia', DB::raw('COUNT(*) as usage_count'), DB::raw('COALESCE(SUM(mm.pills_received),0) as pills'))
            ->groupBy('sh.id', 'sh.name')
            ->orderByDesc('usage_count')
            ->get();

        $data = $rows->map(function($r){ return ['name' => $r->shehia, 'value' => (int)$r->usage_count]; });
        $max = $rows->max('usage_count') ?? 0;
        return [ 'by_shehia' => $data, 'max' => (int)$max ];
    }

    /**
     * Get top diseases for the period
     */
    private function getTopDiseases($dateRange)
    {
        $rows = DB::table('disease_cases as dc')
            ->join('diseases as d', 'd.id', '=', 'dc.disease_id')
            ->whereBetween('dc.reported_date', [$dateRange['start'], $dateRange['end']])
            ->select('d.name', DB::raw('COUNT(*) as case_count'))
            ->groupBy('dc.disease_id', 'd.name')
            ->orderByDesc('case_count')
            ->limit(5)
            ->get();

        return [
            'labels' => $rows->pluck('name')->toArray(),
            'data' => $rows->pluck('case_count')->toArray()
        ];
    }

    /**
     * Get medication prescription trends
     */
    private function getMedicationTrends($dateRange)
    {
        // Top 3 medications in range
        $topMedIds = DB::table('prescriptions')
            ->whereBetween('prescribed_date', [$dateRange['start'], $dateRange['end']])
            ->select('medication_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('medication_id')
            ->orderByDesc('cnt')
            ->limit(3)
            ->pluck('medication_id');

        if ($topMedIds->isEmpty()) {
            return ['labels' => [], 'series' => []];
        }

        // Month labels across the period
        $labels = [];
        $cursor = $dateRange['start']->copy()->startOfMonth();
        $end = $dateRange['end']->copy()->endOfMonth();
        while ($cursor <= $end) {
            $labels[] = $cursor->format('M Y');
            $cursor->addMonth();
        }

        // Aggregate counts per med per month
        $rows = DB::table('prescriptions')
            ->whereBetween('prescribed_date', [$dateRange['start'], $dateRange['end']])
            ->whereIn('medication_id', $topMedIds)
            ->select('medication_id', DB::raw('YEAR(prescribed_date) as y'), DB::raw('MONTH(prescribed_date) as m'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('medication_id', 'y', 'm')
            ->get();

        $names = DB::table('medications')->whereIn('id', $topMedIds)->pluck('name', 'id');

        // Build series in label order
        $series = [];
        foreach ($topMedIds as $medId) {
            $data = [];
            $cursor = $dateRange['start']->copy()->startOfMonth();
            while ($cursor <= $end) {
                $y = $cursor->year; $m = $cursor->month;
                $match = $rows->firstWhere(fn($r) => $r->medication_id == $medId && (int)$r->y === $y && (int)$r->m === $m);
                $data[] = $match ? (int)$match->cnt : 0;
                $cursor->addMonth();
            }
            $series[] = ['name' => $names[$medId] ?? 'Unknown', 'data' => $data];
        }

        return ['labels' => $labels, 'series' => $series];
    }

    /**
     * Get disease heatmap data by district and disease type
     */
    private function getDiseaseHeatmap($dateRange)
    {
        // Top 5 diseases
        $topDiseaseIds = DB::table('disease_cases')
            ->whereBetween('reported_date', [$dateRange['start'], $dateRange['end']])
            ->select('disease_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('disease_id')
            ->orderByDesc('cnt')
            ->limit(5)
            ->pluck('disease_id');

        if ($topDiseaseIds->isEmpty()) {
            return ['series' => [], 'districts' => []];
        }

        $districts = DB::table('districts')->orderBy('name')->pluck('name', 'id');
        $diseaseNames = DB::table('diseases')->whereIn('id', $topDiseaseIds)->pluck('name', 'id');

        $rows = DB::table('disease_cases as dc')
            ->join('patients as p', 'p.id', '=', 'dc.patient_id')
            ->whereBetween('dc.reported_date', [$dateRange['start'], $dateRange['end']])
            ->whereIn('dc.disease_id', $topDiseaseIds)
            ->select('dc.disease_id', 'p.district_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('dc.disease_id', 'p.district_id')
            ->get();

        $series = [];
        foreach ($topDiseaseIds as $did) {
            $data = [];
            foreach ($districts as $distId => $distName) {
                $match = $rows->firstWhere(fn($r) => $r->disease_id == $did && (int)$r->district_id === (int)$distId);
                $data[] = ['x' => $distName, 'y' => $match ? (int)$match->cnt : 0];
            }
            $series[] = ['name' => $diseaseNames[$did] ?? 'Disease', 'data' => $data];
        }

        return ['series' => $series, 'districts' => array_values($districts->toArray())];
    }

    /**
     * Get patient age distribution data
     */
    private function getAgeDistribution($dateRange)
    {
        // Aggregate by app's age_group for patients with activity in range (memory-safe)
        $counts = \App\Models\Patient::query()
            ->where(function($q) use ($dateRange) {
                $q->whereHas('diseaseCases', function($q2) use ($dateRange) {
                    $q2->whereBetween('reported_date', [$dateRange['start'], $dateRange['end']]);
                })->orWhereHas('prescriptions', function($q2) use ($dateRange) {
                    $q2->whereBetween('prescribed_date', [$dateRange['start'], $dateRange['end']]);
                });
            })
            ->select('age_group', DB::raw('COUNT(*) as cnt'))
            ->groupBy('age_group')
            ->pluck('cnt', 'age_group')
            ->toArray();

        $labels = ['0-5', '6-17', '18-35', '36-55', '56+'];
        $series = [];
        foreach ($labels as $g) {
            $series[] = isset($counts[$g]) ? (int) $counts[$g] : 0;
        }

        return [
            'series' => $series,
            'labels' => $labels
        ];
    }

    /**
     * Get chronic diseases data
     */
    private function getChronicDiseases($dateRange)
    {
        // Show all diseases classified as 'chronic' in the selected period (no hard-coded name list)
        $rows = \App\Models\DiseaseCase::query()
            ->join('diseases', 'diseases.id', '=', 'disease_cases.disease_id')
            ->whereBetween('disease_cases.reported_date', [$dateRange['start'], $dateRange['end']])
            ->where('diseases.category', 'chronic')
            ->selectRaw('diseases.name as name, COUNT(*) as case_count')
            ->groupBy('diseases.id', 'diseases.name')
            ->orderByDesc('case_count')
            ->get();

        return [
            'labels' => $rows->pluck('name')->toArray(),
            'data' => $rows->pluck('case_count')->toArray()
        ];
    }

    /**
     * Get facility performance data (simplified)
     */
    private function getFacilityPerformance($dateRange)
    {
        // Memory-safe aggregation: cases per district in the date range
        $rows = DB::table('disease_cases as dc')
            ->join('patients as p', 'p.id', '=', 'dc.patient_id')
            ->join('districts as d', 'd.id', '=', 'p.district_id')
            ->whereBetween('dc.reported_date', [$dateRange['start'], $dateRange['end']])
            ->select('d.name as facility', DB::raw('COUNT(*) as cases'))
            ->groupBy('d.id', 'd.name')
            ->orderByDesc('cases')
            ->limit(5)
            ->get();

        $performanceData = $rows->map(function($row) {
            $qualityScore = rand(2, 5);
            $outcomePercentage = rand(60, 95);
            return [
                'facility' => $row->facility,
                'quality' => $qualityScore,
                'outcome' => $outcomePercentage,
                'cases' => (int) $row->cases,
            ];
        });

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

    // API: Facilities list
    public function getFacilities(Request $request)
    {
        try {
            $rows = DB::table('hfacilities')->select('id', 'name')->orderBy('name')->get();
            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch facilities list'], 200);
        }
    }

    // API: Facility attendance per month for a given facility and period
    public function getFacilityAttendance(Request $request)
    {
        $facilityId = (int) $request->input('facility_id');
        $period = $request->input('period', 'this_year');
        if (!$facilityId) {
            return response()->json(['success' => false, 'message' => 'facility_id is required'], 200);
        }
        try {
            $dateRange = $this->calculateDateRange($period);

            // Always show Jan..Dec; aggregate across selected period
            $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

            // Aggregate visits per month-of-year, with fallback to VisitData if visits table missing/empty
            $rows = collect();
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('visits')) {
                    $rows = DB::table('visits')
                        ->where('hfacility_id', $facilityId)
                        ->whereBetween(DB::raw('COALESCE(date_from, date_to)'), [$dateRange['start'], $dateRange['end']])
                        ->selectRaw('MONTH(COALESCE(date_from, date_to)) as m, COUNT(*) as cnt')
                        ->groupBy('m')
                        ->get();
                }
            } catch (\Throwable $e) {
                // ignore and try fallback
            }

            if ($rows->isEmpty()) {
                $source = null;
                if (\Illuminate\Support\Facades\Schema::hasTable('VisitData')) $source = 'VisitData';
                elseif (\Illuminate\Support\Facades\Schema::hasTable('stage_VisitData')) $source = 'stage_VisitData';

                if ($source) {
                    // Attempt to parse DateFrom into DATE with multiple formats, including epoch and Excel serial date
                    $dtExpr = "CASE\n                        WHEN DateFrom REGEXP '^[0-9]{10}$' THEN FROM_UNIXTIME(CAST(DateFrom AS UNSIGNED))\n                        WHEN DateFrom REGEXP '^[0-9]{5}$' THEN DATE_ADD('1899-12-30', INTERVAL CAST(DateFrom AS UNSIGNED) DAY)\n                        ELSE COALESCE(\n                            STR_TO_DATE(DateFrom, '%Y-%m-%d'),\n                            STR_TO_DATE(DateFrom, '%d/%m/%Y'),\n                            STR_TO_DATE(DateFrom, '%m/%d/%Y'),\n                            STR_TO_DATE(DateFrom, '%Y%m%d')\n                        )\n                    END";
                    $rows = DB::table($source)
                        ->where('HFID', $facilityId)
                        ->whereBetween(DB::raw($dtExpr), [$dateRange['start']->format('Y-m-d'), $dateRange['end']->format('Y-m-d')])
                        ->selectRaw("MONTH($dtExpr) as m, COUNT(*) as cnt")
                        ->groupBy('m')
                        ->get();
                }
            }

            // Map results to Jan..Dec order
            $seriesData = [];
            for ($m = 1; $m <= 12; $m++) {
                $match = $rows->firstWhere(fn($r) => (int)$r->m === $m);
                $seriesData[] = $match ? (int)$match->cnt : 0;
            }

            // Facility name
            $facility = DB::table('hfacilities')->where('id', $facilityId)->select('id','name')->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'series' => [[ 'name' => 'Attendance', 'data' => $seriesData ]],
                    'facility' => $facility,
                    'period' => $period
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch attendance'], 200);
        }
    }

    // API: Shehia stats (disease cases + medication prescribed)
    public function getShehiaStats(Request $request)
    {
        $period = $request->input('period', 'this_year');
        $dateRange = $this->calculateDateRange($period);

        // Cache key per period to avoid recomputation
        $cacheKey = 'shehia_stats:'.$period;
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return response()->json(\Illuminate\Support\Facades\Cache::get($cacheKey));
        }

        $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        // Prefer normalized path only; skip heavy VisitData fallback unless needed
        $casesByShehia = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('visit_diagnoses') && \Illuminate\Support\Facades\Schema::hasTable('visits')) {
                $casesByShehia = DB::table('visit_diagnoses as vd')
                    ->join('visits as v', 'v.id', '=', 'vd.visit_id')
                    ->leftJoin('hfacilities as hf', 'hf.id', '=', 'v.hfacility_id')
                    ->leftJoin('shehia as sh', 'sh.id', '=', 'hf.shehia_id')
                    ->whereBetween('v.date_from', [$dateRange['start'], $dateRange['end']])
                    ->selectRaw('COALESCE(sh.name, "Unknown") as shehia, COUNT(*) as disease_cases')
                    ->groupBy('shehia')
                    ->get();
            }
        } catch (\Throwable $e) { /* ignore */ }

        if ($casesByShehia->isEmpty()) {
            // Fallback only if normalized tables not available
            $source = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('VisitData')) $source = 'VisitData';
            elseif (\Illuminate\Support\Facades\Schema::hasTable('stage_VisitData')) $source = 'stage_VisitData';

            if ($source) {
                $dtExpr = "CASE\n                        WHEN DateFrom REGEXP '^[0-9]{10}$' THEN FROM_UNIXTIME(CAST(DateFrom AS UNSIGNED))\n                        WHEN DateFrom REGEXP '^[0-9]{5}$' THEN DATE_ADD('1899-12-30', INTERVAL CAST(DateFrom AS UNSIGNED) DAY)\n                        ELSE COALESCE(\n                            STR_TO_DATE(DateFrom, '%Y-%m-%d'),\n                            STR_TO_DATE(DateFrom, '%d/%m/%Y'),\n                            STR_TO_DATE(DateFrom, '%m/%d/%Y'),\n                            STR_TO_DATE(DateFrom, '%Y%m%d')\n                        )\n                    END";
                $casesByShehia = DB::table($source)
                    ->whereBetween(DB::raw($dtExpr), [$dateRange['start']->format('Y-m-d'), $dateRange['end']->format('Y-m-d')])
                    ->whereNotNull('Shehia')
                    ->selectRaw('TRIM(Shehia) as shehia, COUNT(*) as disease_cases')
                    ->groupBy('shehia')
                    ->get();
            }
        }

        // Medications prescribed by shehia via patients->prescriptions
        $medByShehia = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('prescriptions') && \Illuminate\Support\Facades\Schema::hasTable('patients')) {
                $medByShehia = DB::table('prescriptions as pr')
                    ->join('patients as p', 'p.id', '=', 'pr.patient_id')
                    ->leftJoin('shehia as sh', 'sh.id', '=', 'p.shehia_id')
                    ->whereBetween('pr.prescribed_date', [$dateRange['start'], $dateRange['end']])
                    ->selectRaw('COALESCE(sh.name, "Unknown") as shehia, COUNT(*) as meds_prescribed')
                    ->groupBy('shehia')
                    ->get();
            }
        } catch (\Throwable $e) { /* ignore */ }

        $shehiaNames = collect($casesByShehia)->pluck('shehia')->merge(collect($medByShehia)->pluck('shehia'))->unique()->values();
        $stats = [];
        foreach ($shehiaNames as $shName) {
            $cases = optional($casesByShehia->firstWhere('shehia', $shName))->disease_cases ?? 0;
            $meds  = optional($medByShehia->firstWhere('shehia', $shName))->meds_prescribed ?? 0;
            $stats[] = [
                'shehia' => $shName,
                'disease_cases' => (int)$cases,
                'meds_prescribed' => (int)$meds,
            ];
        }

        $payload = [
            'success' => true,
            'data' => [
                'labels' => $labels,
                'stats' => $stats,
            ]
        ];

        // Cache for 30 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $payload, now()->addMinutes(30));
        return response()->json($payload);
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
