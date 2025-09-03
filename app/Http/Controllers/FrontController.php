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
            $period = $request->input('period', 'since_2020'); // Default since 2020

            // Calculate date range based on period
            $dateRange = $this->calculateDateRange($period);

            // Cache by period and end date for 5 minutes
            $cacheKey = 'dashboard:v4:'. $period .':'. $dateRange['end']->format('Y-m-d');
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
        // Total patients from visit_data (distinct matibabu_id, ignore blanks) within date range
        $totalPatients = DB::table('visit_data')
            ->whereBetween('date_from', [$dateRange['start'], $dateRange['end']])
            ->selectRaw("COUNT(DISTINCT NULLIF(TRIM(matibabu_id), '')) as c")
            ->value('c');

        // Total health facilities from visit_data (all-time, no date filter)
        $totalFacilities = DB::table('visit_data')->selectRaw('COUNT(DISTINCT hf_id) as c')->value('c');

        // Under-5 priority disease cases in date range (Malaria, Pneumonia, Diarrhea)
        // Age computed at visit date; diseases matched via ICD-10 code prefixes
        $totalU5 = DB::table('visit_data as v')
            ->join('icd_codes as ic', 'ic.icd_id', '=', 'v.icd_id')
            ->whereBetween('v.date_from', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('v.dob')
            ->whereRaw('TIMESTAMPDIFF(YEAR, v.dob, v.date_from) < 5')
            ->whereRaw("ic.icd_code REGEXP '^(B5[0-4]|J1[2-8]|A09|K52|R19\\.7)'")
            ->count();

        // Pregnancy cases: any risk_profile entry with claim mapped in selected period
        $pregnancyCases = DB::table('risk_profile as rp')
            ->join('visit_data as v', 'v.claim_id', '=', 'rp.claim_id')
            ->whereBetween('v.date_from', [$dateRange['start'], $dateRange['end']])
            ->count();

        // Chronic diseases cases (example set; adjust list as needed)
        $chronicCases = DB::table('visit_data as v')
            ->join('icd_codes as ic', 'ic.icd_id', '=', 'v.icd_id')
            ->whereBetween('v.date_from', [$dateRange['start'], $dateRange['end']])
            ->whereRaw("ic.icd_code REGEXP '^(E1[0-4]|I1[0-5]|J4[0-7]|N18|K70|K74|C[0-9]{2})'")
            ->count();



        // Calculate percentage changes (compared to previous period)
        $previousRange = $this->getPreviousPeriodRange($dateRange);

        // Previous period metrics using visit_data as source
        $previousPatients = DB::table('visit_data')
            ->whereBetween('date_from', [$previousRange['start'], $previousRange['end']])
            ->selectRaw("COUNT(DISTINCT NULLIF(TRIM(matibabu_id), '')) as c")
            ->value('c');

        $previousU5 = DB::table('visit_data as v')
            ->join('icd_codes as ic', 'ic.icd_id', '=', 'v.icd_id')
            ->whereBetween('v.date_from', [$previousRange['start'], $previousRange['end']])
            ->whereNotNull('v.dob')
            ->whereRaw('TIMESTAMPDIFF(YEAR, v.dob, v.date_from) < 5')
            ->whereRaw("ic.icd_code REGEXP '^(B5[0-4]|J1[2-8]|A09|K52|R19\\.7)'")
            ->count();

        $previousDiseaseCases = DB::table('visit_data as v')
            ->join('icd_codes as ic', 'ic.icd_id', '=', 'v.icd_id')
            ->whereBetween('v.date_from', [$previousRange['start'], $previousRange['end']])
            ->count();

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
            // Under-5 (Malaria/Pneumonia/Diarrhea)
            'prescriptions' => [
                'value' => number_format($totalU5),
                'change' => $this->calculatePercentageChange($totalU5, $previousU5 ?? 0),
                'trend' => ($totalU5 >= ($previousU5 ?? 0)) ? 'up' : 'down'
            ],
            // Pregnancy cases
            'pregnancy_cases' => [
                'value' => number_format($pregnancyCases),
                'change' => '+0%',
                'trend' => 'stable'
            ],
            // Chronic diseases cases
            'chronic_cases' => [
                'value' => number_format($chronicCases),
                'change' => '+0%',
                'trend' => 'stable'
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
        $safe = function(callable $fn, $default) {
            try { return $fn(); } catch (\Throwable $e) { \Log::warning('Chart block failed', ['err'=>$e->getMessage()]); return $default; }
        };

        return [
            'top_diseases' => $safe(fn() => $this->getTopDiseases($dateRange), ['labels'=>[], 'data'=>[]]),
            'medication_trends' => $safe(fn() => $this->getMedicationTrends($dateRange), ['labels'=>[], 'series'=>[]]),
            'disease_heatmap' => $safe(fn() => $this->getDiseaseHeatmap($dateRange), ['series'=>[], 'districts'=>[]]),
            'medication_geo' => $safe(fn() => $this->getMedicationGeoHeatmap($dateRange), ['by_shehia'=>[], 'max'=>0]),
            'chronic_diseases' => $safe(fn() => $this->getChronicDiseases($dateRange), ['labels'=>[], 'data'=>[]]),
            'facility_performance' => $safe(fn() => $this->getFacilityPerformance($dateRange), []),
            'age_distribution' => $safe(fn() => $this->getAgeDistribution($dateRange), ['labels'=>['0-5','6-17','18-35','36-55','56+'], 'series'=>[0,0,0,0,0]]),
            'pregnancy_trends' => $safe(fn() => $this->getPregnancyTrends($dateRange), ['labels'=>[], 'series'=>[]]),
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
        // Top diseases from visit_data + icd_codes within the period
        $rows = DB::table('visit_data as v')
            ->join('icd_codes as ic', 'ic.icd_id', '=', 'v.icd_id')
            ->whereBetween('v.date_from', [$dateRange['start'], $dateRange['end']])
            ->select('ic.icd_name as name', DB::raw('COUNT(*) as case_count'))
            ->groupBy('ic.icd_id', 'ic.icd_name')
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
        // Based on visit_data + icd_codes: top 3 ICD codes by count and their monthly counts
        $topIcdIds = DB::table('visit_data as v')
            ->whereBetween('v.date_from', [$dateRange['start'], $dateRange['end']])
            ->select('v.icd_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('v.icd_id')
            ->orderByDesc('cnt')
            ->limit(3)
            ->pluck('icd_id');

        if ($topIcdIds->isEmpty()) {
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

        // Aggregate counts per icd per month
        $rows = DB::table('visit_data as v')
            ->whereBetween('v.date_from', [$dateRange['start'], $dateRange['end']])
            ->whereIn('v.icd_id', $topIcdIds)
            ->select('v.icd_id', DB::raw('YEAR(v.date_from) as y'), DB::raw('MONTH(v.date_from) as m'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('v.icd_id', 'y', 'm')
            ->get();

        $names = DB::table('icd_codes')->whereIn('icd_id', $topIcdIds)->pluck('icd_name', 'icd_id');

        // Build series in label order
        $series = [];
        foreach ($topIcdIds as $icdId) {
            $data = [];
            $cursor = $dateRange['start']->copy()->startOfMonth();
            while ($cursor <= $end) {
                $y = $cursor->year; $m = $cursor->month;
                $match = $rows->firstWhere(fn($r) => $r->icd_id == $icdId && (int)$r->y === $y && (int)$r->m === $m);
                $data[] = $match ? (int)$match->cnt : 0;
                $cursor->addMonth();
            }
            $series[] = ['name' => $names[$icdId] ?? 'ICD '.$icdId, 'data' => $data];
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
        // Compute age at visit for all visits in range, bucket into groups
        $rows = DB::table('visit_data')
            ->whereBetween('date_from', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('dob')
            ->selectRaw("CASE
                WHEN TIMESTAMPDIFF(YEAR, dob, date_from) <= 5 THEN '0-5'
                WHEN TIMESTAMPDIFF(YEAR, dob, date_from) <= 17 THEN '6-17'
                WHEN TIMESTAMPDIFF(YEAR, dob, date_from) <= 35 THEN '18-35'
                WHEN TIMESTAMPDIFF(YEAR, dob, date_from) <= 55 THEN '36-55'
                ELSE '56+'
            END as age_group, COUNT(*) as cnt")
            ->groupBy('age_group')
            ->pluck('cnt', 'age_group');

        $labels = ['0-5', '6-17', '18-35', '36-55', '56+'];
        $series = [];
        foreach ($labels as $g) {
            $series[] = (int) ($rows[$g] ?? 0);
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
        // Chronic diseases: Diabetes (E10–E14) and Hypertension (I10–I15) from visit_data + icd_codes
        $rows = DB::table('visit_data as v')
            ->join('icd_codes as ic', 'ic.icd_id', '=', 'v.icd_id')
            ->whereBetween('v.date_from', [$dateRange['start'], $dateRange['end']])
            ->whereRaw("ic.icd_code REGEXP '^(E1[0-4]|I1[0-5])'")
            ->selectRaw("CASE
                WHEN ic.icd_code REGEXP '^E1[0-4]' THEN 'Diabetes'
                WHEN ic.icd_code REGEXP '^I1[0-5]' THEN 'Hypertension'
                ELSE 'Other'
            END as name, COUNT(*) as case_count")
            ->groupBy('name')
            ->orderByDesc('case_count')
            ->get();

        return [
            'labels' => $rows->pluck('name')->toArray(),
            'data' => $rows->pluck('case_count')->toArray()
        ];
    }

    /**
     * High-risk pregnancy trends over months (count of unique patients with risk_profile)
     */
    private function getPregnancyTrends($dateRange)
    {
        // Build month labels
        $labels = [];
        $cursor = $dateRange['start']->copy()->startOfMonth();
        $end = $dateRange['end']->copy()->endOfMonth();
        while ($cursor <= $end) {
            $labels[] = $cursor->format('M Y');
            $cursor->addMonth();
        }

        // Aggregate unique patients per month who have risk_profile entries
        $rows = DB::table('risk_profile as rp')
            ->join('visit_data as v', 'v.claim_id', '=', 'rp.claim_id')
            ->whereBetween('v.date_from', [$dateRange['start'], $dateRange['end']])
            ->selectRaw("YEAR(v.date_from) as y, MONTH(v.date_from) as m, COUNT(DISTINCT NULLIF(TRIM(v.matibabu_id), '')) as cnt")
            ->groupBy('y','m')
            ->get();

        // Map to labels order
        $series = [];
        $cursor = $dateRange['start']->copy()->startOfMonth();
        while ($cursor <= $end) {
            $y = $cursor->year; $m = $cursor->month;
            $match = $rows->firstWhere(fn($r) => (int)$r->y === (int)$y && (int)$r->m === (int)$m);
            $series[] = $match ? (int)$match->cnt : 0;
            $cursor->addMonth();
        }

        return [
            'labels' => $labels,
            'series' => [ ['name' => 'Pregnancy Patients', 'data' => $series] ]
        ];
    }



    /**
     * Get facility performance data (simplified)
     */
    private function getFacilityPerformance($dateRange)
    {
        // Attendance of patients per health facility (visit_data only)
        // Attendance = distinct patients (matibabu_id, ignoring blanks) within the selected period
        $rows = DB::table('visit_data as v')
            ->whereBetween('v.date_from', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('v.hf_id')
            ->selectRaw("v.hf_id as hf_id, COALESCE(NULLIF(MAX(TRIM(v.hf_name)),''), CONCAT('HF ', v.hf_id)) as facility, COUNT(DISTINCT NULLIF(TRIM(v.matibabu_id),'')) as attendance, COUNT(*) as visits")
            ->groupBy('v.hf_id')
            ->orderByDesc('attendance')
            ->limit(10)
            ->get();

        // Return as array of { facility, attendance, visits }
        return $rows->map(function($r){
            return [
                'facility' => (string)$r->facility,
                'attendance' => (int)($r->attendance ?? 0),
                'visits' => (int)($r->visits ?? 0),
            ];
        })->values();
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
            // Load facilities from visit_data only (group by hf_id; derive a safe label)
            $rows = DB::table('visit_data')
                ->whereNotNull('hf_id')
                ->groupBy('hf_id')
                ->selectRaw("hf_id as id, COALESCE(NULLIF(MAX(TRIM(hf_name)) ,''), CONCAT('HF ', hf_id)) as name")
                ->orderBy('name')
                ->get();
            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch facilities list'], 200);
        }
    }

    // API: Facility attendance per month for a given facility and period (visit_data only)
    public function getFacilityAttendance(Request $request)
    {
        $facilityId = $request->input('facility_id');
        $period = $request->input('period', 'this_year');
        if ($facilityId === null || $facilityId === '') {
            return response()->json(['success' => false, 'message' => 'facility_id is required'], 200);
        }
        try {
            $dateRange = $this->calculateDateRange($period);

            // Always show Jan..Dec; accumulate across years in the selected period
            $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

            // Aggregate visits per month-of-year (accumulate across years)
            $rows = DB::table('visit_data')
                ->where('hf_id', $facilityId)
                ->whereBetween('date_from', [$dateRange['start'], $dateRange['end']])
                ->selectRaw('MONTH(date_from) as m, COUNT(*) as cnt')
                ->groupBy(DB::raw('MONTH(date_from)'))
                ->orderBy('m')
                ->get();

            // Map results to Jan..Dec
            $seriesData = [];
            for ($m = 1; $m <= 12; $m++) {
                $match = $rows->firstWhere(fn($r) => (int)$r->m === $m);
                $seriesData[] = $match ? (int)$match->cnt : 0;
            }

            // Facility from visit_data only (avoid ONLY_FULL_GROUP_BY issues)
            $nameRow = DB::table('visit_data')
                ->where('hf_id', $facilityId)
                ->selectRaw("MAX(NULLIF(TRIM(hf_name), '')) as name")
                ->first();
            $facility = [ 'id' => (int)$facilityId, 'name' => $nameRow ? $nameRow->name : null ];

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'series' => [[ 'name' => 'Attendance', 'data' => $seriesData ]],
                    'facility' => $facility ?: ['id' => $facilityId, 'name' => null],
                    'period' => $period
                ]
            ]);
        } catch (\Throwable $e) {
            \Log::error('Facility attendance API failed', [
                'facility_id' => $facilityId,
                'period' => $period,
                'error' => $e->getMessage()
            ]);
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
            // Available medications from items table only (top by total quantity)
            $availableMedications = DB::table('items')
                ->select('item_id as id', DB::raw('MAX(item_name) as name'), DB::raw('SUM(COALESCE(qty_provided,1)) as total_usage'))
                ->groupBy('item_id')
                ->orderByDesc('total_usage')
                ->limit(200)
                ->get();

            // Get available years from visit_data
            $availableYears = DB::table('visit_data')->selectRaw('DISTINCT YEAR(date_from) as year')
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($availableYears)) {
                $availableYears = [date('Y')];
            }

            // Set default years
            $currentYear = date('Y');
            $startYear = !empty($availableYears) ? min($availableYears) : 2020;

            // Get total medication usage (sum of quantities)
            $totalPrescriptions = (int) DB::table('items')->sum(DB::raw('COALESCE(qty_provided,1)'));

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
        // Render the chronic diseases analytics page (data loaded via API using visit_data + icd_codes)
        return view('frontend.chronic');
    }

    public function showTopDiseases()
    {
        // Get available diseases and years for filters
        $availableDiseases = DB::table('icd_codes')->select('icd_id as id', 'icd_name as name')->orderBy('icd_name')->get();

        $availableYears = DB::table('visit_data')->selectRaw('YEAR(date_from) as year')
            ->distinct()
            ->orderBy('year')
            ->pluck('year')
            ->toArray();

        // Get current year for default filter
        $currentYear = date('Y');
        $startYear = !empty($availableYears) ? min($availableYears) : 2020;

        // Get initial data for the table (top diseases by case count) from visit_data + icd_codes in current year
        $diseases = DB::table('visit_data as v')
            ->join('icd_codes as ic', 'ic.icd_id', '=', 'v.icd_id')
            ->whereYear('v.date_from', date('Y'))
            ->select('ic.icd_name as name', DB::raw('COUNT(*) as cases'))
            ->groupBy('ic.icd_id', 'ic.icd_name')
            ->orderByDesc('cases')
            ->limit(10)
            ->get()
            ->map(function ($r) { return ['name' => $r->name, 'cases' => (int)$r->cases]; })
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
