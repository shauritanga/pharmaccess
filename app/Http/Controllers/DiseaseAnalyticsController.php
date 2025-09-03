<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Disease;
use App\Models\District;
use App\Models\Patient;
use App\Models\DiseaseCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DiseaseAnalyticsController extends Controller
{
    /**
     * Get analytics data for top diseases page
     */
    public function getAnalyticsData(Request $request)
    {
        // Validate and sanitize inputs
        $diseases = $request->input('diseases', []);
        $yearStart = max(2020, (int) $request->input('year_start', 2020));
        $yearEnd = min(date('Y'), (int) $request->input('year_end', date('Y')));

        // Ensure year range is valid
        if ($yearStart > $yearEnd) {
            $yearStart = $yearEnd;
        }

        // Filter out invalid ICD IDs (use icd_codes)
        if (!empty($diseases)) {
            $validIcdIds = DB::table('icd_codes')->whereIn('icd_id', $diseases)->pluck('icd_id')->toArray();
            $diseases = array_values(array_intersect($diseases, $validIcdIds));
        }

        // Create cache key based on filters (bump version to invalidate old structures)
        $cacheKey = "disease_analytics_v2_" . md5(serialize([
            'diseases' => $diseases,
            'year_start' => $yearStart,
            'year_end' => $yearEnd,
            'econ_version' => 2
        ]));

        // Cache for 1 hour
        $data = Cache::remember($cacheKey, 3600, function () use ($diseases, $yearStart, $yearEnd) {
            return [
                'monthly_distribution' => $this->getMonthlyDistribution($diseases, $yearStart, $yearEnd),
                'gender_distribution' => $this->getGenderDistribution($diseases, $yearStart, $yearEnd),
                'age_group_distribution' => $this->getAgeGroupDistribution($diseases, $yearStart, $yearEnd),
                'economic_status_distribution' => $this->getEconomicStatusDistribution($diseases, $yearStart, $yearEnd),
                'district_distribution' => $this->getDistrictDistribution($diseases, $yearStart, $yearEnd),
                'heatmap_data' => $this->getHeatmapData($diseases, $yearStart, $yearEnd),
            ];
        });

        return response()->json([
            'success' => true,
            'filters' => [
                'diseases' => $diseases,
                'year_range' => [$yearStart, $yearEnd]
            ],
            'charts' => $data
        ]);
    }

    /**
     * Get monthly distribution of cases (from visit_data + icd_codes)
     */
    private function getMonthlyDistribution($diseases, $yearStart, $yearEnd)
    {
        $start = Carbon::create($yearStart, 1, 1)->startOfDay();
        $end   = Carbon::create($yearEnd, 12, 31)->endOfDay();

        $q = DB::table('visit_data as v')
            ->whereBetween('v.date_from', [$start, $end]);

        if (!empty($diseases)) {
            $q->whereIn('v.icd_id', $diseases);
        }

        $monthly = $q->selectRaw('MONTH(v.date_from) as m, COUNT(*) as cases')
            ->groupBy('m')
            ->orderBy('m')
            ->get()
            ->pluck('cases', 'm')
            ->toArray();

        $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $data = [];
        for ($i = 1; $i <= 12; $i++) { $data[] = $monthly[$i] ?? 0; }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Get gender distribution of cases (male/female only) from visit_data
     */
    private function getGenderDistribution($diseases, $yearStart, $yearEnd)
    {
        $start = Carbon::create($yearStart, 1, 1)->startOfDay();
        $end   = Carbon::create($yearEnd, 12, 31)->endOfDay();

        $q = DB::table('visit_data as v')->whereBetween('v.date_from', [$start, $end]);
        if (!empty($diseases)) { $q->whereIn('v.icd_id', $diseases); }

        // Normalize gender to male/female
        $rows = $q->selectRaw("CASE
                WHEN UPPER(TRIM(v.gender)) IN ('M','MALE') THEN 'male'
                WHEN UPPER(TRIM(v.gender)) IN ('F','FEMALE') THEN 'female'
                ELSE NULL END as g, COUNT(*) as cases")
            ->whereNotNull('v.gender')
            ->groupBy('g')
            ->get()
            ->pluck('cases', 'g')
            ->toArray();

        return [
            'labels' => ['Male', 'Female'],
            'data' => [ (int)($rows['male'] ?? 0), (int)($rows['female'] ?? 0) ]
        ];
    }

    /**
     * Get age group distribution of cases (computed from dob vs date_from)
     */
    private function getAgeGroupDistribution($diseases, $yearStart, $yearEnd)
    {
        $start = Carbon::create($yearStart, 1, 1)->startOfDay();
        $end   = Carbon::create($yearEnd, 12, 31)->endOfDay();

        $q = DB::table('visit_data as v')->whereBetween('v.date_from', [$start, $end]);
        if (!empty($diseases)) { $q->whereIn('v.icd_id', $diseases); }

        $rows = $q->whereNotNull('v.dob')
            ->selectRaw("CASE
                WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) BETWEEN 0 AND 5 THEN '0-5'
                WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) BETWEEN 6 AND 17 THEN '6-17'
                WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) BETWEEN 18 AND 35 THEN '18-35'
                WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) BETWEEN 36 AND 55 THEN '36-55'
                WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) >= 56 THEN '56+'
                ELSE 'Unknown' END as grp, COUNT(*) as cases")
            ->groupBy('grp')
            ->get()
            ->pluck('cases', 'grp')
            ->toArray();

        $labels = ['0-5','6-17','18-35','36-55','56+'];
        $data = [];
        foreach ($labels as $g) { $data[] = (int)($rows[$g] ?? 0); }
        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Get economic status distribution of cases using visit_data.ppi_score
     * Rules: ppi_score > 26 => High, otherwise Low. Only two buckets: Low, High.
     */
    private function getEconomicStatusDistribution($diseases, $yearStart, $yearEnd)
    {
        $start = Carbon::create($yearStart, 1, 1)->startOfDay();
        $end   = Carbon::create($yearEnd, 12, 31)->endOfDay();

        $q = DB::table('visit_data as v')->whereBetween('v.date_from', [$start, $end]);
        if (!empty($diseases)) { $q->whereIn('v.icd_id', $diseases); }

        // Treat NULL or empty ppi_score as Low by default
        $rows = $q->selectRaw("CASE
                WHEN CAST(NULLIF(TRIM(v.ppi_score), '') AS UNSIGNED) > 26 THEN 'High'
                ELSE 'Low' END as econ, COUNT(*) as cases")
            ->groupBy('econ')
            ->get()
            ->pluck('cases', 'econ')
            ->toArray();

        $labels = ['Low', 'High'];
        $data = [ (int)($rows['Low'] ?? 0), (int)($rows['High'] ?? 0) ];
        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Get district distribution of cases (use visit_data.district_name)
     */
    private function getDistrictDistribution($diseases, $yearStart, $yearEnd)
    {
        $start = Carbon::create($yearStart, 1, 1)->startOfDay();
        $end   = Carbon::create($yearEnd, 12, 31)->endOfDay();

        $q = DB::table('visit_data as v')->whereBetween('v.date_from', [$start, $end]);
        if (!empty($diseases)) { $q->whereIn('v.icd_id', $diseases); }

        $rows = $q->selectRaw("COALESCE(NULLIF(TRIM(v.district_name), ''), 'Unknown') as name, COUNT(*) as cases")
            ->groupBy('name')
            ->orderByDesc('cases')
            ->get();

        return [
            'labels' => $rows->pluck('name')->toArray(),
            'data' => $rows->pluck('cases')->toArray()
        ];
    }

    /**
     * Get heatmap data (disabled as districts lat/lng/population not available reliably)
     */
    private function getHeatmapData($diseases, $yearStart, $yearEnd)
    {
        return [];
    }

    /**
     * Get available diseases for filter dropdown (most-used from visit_data + icd_codes), with optional search and limit.
     * Query params: q (search term), limit (default 200)
     */
    public function getAvailableDiseases(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $limit = (int)($request->input('limit', 200));
        if ($limit <= 0 || $limit > 1000) { $limit = 200; }

        $base = DB::table('visit_data as v')
            ->join('icd_codes as ic', 'ic.icd_id', '=', 'v.icd_id');

        if ($q !== '') {
            $base->where(function($w) use ($q) {
                $w->where('ic.icd_code', 'like', "%$q%")
                  ->orWhere('ic.icd_name', 'like', "%$q%{}");
            });
        }

        $rows = $base->select('ic.icd_id as id', 'ic.icd_code', 'ic.icd_name', DB::raw('COUNT(*) as cnt'))
            ->groupBy('ic.icd_id', 'ic.icd_code', 'ic.icd_name')
            ->orderByDesc('cnt')
            ->limit($limit)
            ->get();

        $diseases = $rows->map(function ($r) {
            return [ 'id' => (int)$r->id, 'name' => $r->icd_name, 'code' => $r->icd_code, 'count' => (int)$r->cnt ];
        });

        return response()->json(['success' => true, 'diseases' => $diseases]);
    }

    /**
     * Get available years for filter dropdown (from visit_data)
     */
    public function getAvailableYears()
    {
        $years = DB::table('visit_data')->selectRaw('YEAR(date_from) as year')
            ->distinct()->orderBy('year')->pluck('year')->toArray();
        return response()->json(['success' => true, 'years' => $years]);
    }
}
