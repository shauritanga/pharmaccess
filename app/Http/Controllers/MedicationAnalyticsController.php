<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Medication;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\District;

class MedicationAnalyticsController extends Controller
{
    /**
     * Get comprehensive medication analytics data
     */
    public function getAnalyticsData(Request $request)
    {
        try {
            // Handle medications parameter - can be string or array
            $medications = $request->input('medications', []);
            if (is_string($medications) && !empty($medications)) {
                $medications = [$medications];
            } elseif (!is_array($medications)) {
                $medications = [];
            }

            $yearStart = $request->input('year_start', date('Y'));
            $yearEnd = $request->input('year_end', date('Y'));

            // Create cache key
            $cacheKey = 'medication_analytics_' . md5(json_encode([
                'medications' => $medications,
                'year_start' => $yearStart,
                'year_end' => $yearEnd
            ]));

            // Cache for 1 hour
            $data = Cache::remember($cacheKey, 3600, function () use ($medications, $yearStart, $yearEnd) {
                return [
                    'monthly_distribution' => $this->getMonthlyDistribution($medications, $yearStart, $yearEnd),
                    'gender_distribution' => $this->getGenderDistribution($medications, $yearStart, $yearEnd),
                    'age_group_distribution' => $this->getAgeGroupDistribution($medications, $yearStart, $yearEnd),
                    'economic_status_distribution' => $this->getEconomicStatusDistribution($medications, $yearStart, $yearEnd),
                    'district_distribution' => $this->getDistrictDistribution($medications, $yearStart, $yearEnd),
                    'heatmap_data' => $this->getHeatmapData($medications, $yearStart, $yearEnd),
                ];
            });

            return response()->json([
                'success' => true,
                'charts' => $data,
                'filters' => [
                    'medications' => $medications,
                    'year_start' => $yearStart,
                    'year_end' => $yearEnd
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch medication analytics data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly distribution of prescriptions (for line chart trends)
     */
    private function getMonthlyDistribution($medications, $yearStart, $yearEnd)
    {
        $q = DB::table('items as i')
            ->join('visit_data as v', 'v.claim_id', '=', 'i.claim_id')
            ->whereBetween('v.date_from', ["$yearStart-01-01", "$yearEnd-12-31"]);

        if (!empty($medications)) {
            $q->whereIn('i.item_id', $medications);
        }

        $monthlyData = $q->select(
                DB::raw('YEAR(v.date_from) as year'),
                DB::raw('MONTH(v.date_from) as month'),
                DB::raw('SUM(COALESCE(i.qty_provided,1)) as total_usage')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Create labels and data arrays for line chart
        $labels = [];
        $data = [];

        for ($year = (int)$yearStart; $year <= (int)$yearEnd; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $monthName = date('M Y', mktime(0, 0, 0, $month, 1, $year));
                $labels[] = $monthName;

                $row = $monthlyData
                    ->firstWhere(function ($r) use ($year, $month) { return (int)$r->year === (int)$year && (int)$r->month === (int)$month; });

                $data[] = $row ? (int)$row->total_usage : 0;
            }
        }

        return [ 'labels' => $labels, 'data' => $data ];
    }

    /**
     * Get gender distribution of patients receiving prescriptions
     */
    private function getGenderDistribution($medications, $yearStart, $yearEnd)
    {
        $q = DB::table('items as i')
            ->join('visit_data as v', 'v.claim_id', '=', 'i.claim_id')
            ->whereBetween('v.date_from', ["$yearStart-01-01", "$yearEnd-12-31"]);

        if (!empty($medications)) { $q->whereIn('i.item_id', $medications); }

        $rows = $q->selectRaw("CASE
                WHEN UPPER(TRIM(v.gender)) IN ('M','MALE') THEN 'male'
                WHEN UPPER(TRIM(v.gender)) IN ('F','FEMALE') THEN 'female'
                ELSE NULL END as g, SUM(COALESCE(i.qty_provided,1)) as total_usage")
            ->whereNotNull('v.gender')
            ->groupBy('g')
            ->get()
            ->pluck('total_usage', 'g')
            ->toArray();

        return [ 'labels' => ['Male','Female'], 'data' => [ (int)($rows['male'] ?? 0), (int)($rows['female'] ?? 0) ] ];
    }

    /**
     * Get age group distribution
     */
    private function getAgeGroupDistribution($medications, $yearStart, $yearEnd)
    {
        $q = DB::table('items as i')
            ->join('visit_data as v', 'v.claim_id', '=', 'i.claim_id')
            ->whereBetween('v.date_from', ["$yearStart-01-01", "$yearEnd-12-31"]);

        if (!empty($medications)) { $q->whereIn('i.item_id', $medications); }

        $rows = $q->whereNotNull('v.dob')
            ->selectRaw("CASE
                WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) BETWEEN 0 AND 5 THEN '0-5'
                WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) BETWEEN 6 AND 17 THEN '6-17'
                WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) BETWEEN 18 AND 35 THEN '18-35'
                WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) BETWEEN 36 AND 55 THEN '36-55'
                WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) >= 56 THEN '56+'
                ELSE 'Unknown' END as grp, SUM(COALESCE(i.qty_provided,1)) as total_usage")
            ->groupBy('grp')
            ->get()
            ->pluck('total_usage', 'grp')
            ->toArray();

        $labels = ['0-5','6-17','18-35','36-55','56+'];
        $data = [];
        foreach ($labels as $g) { $data[] = (int)($rows[$g] ?? 0); }
        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Get economic status distribution
     */
    private function getEconomicStatusDistribution($medications, $yearStart, $yearEnd)
    {
        $q = DB::table('items as i')
            ->join('visit_data as v', 'v.claim_id', '=', 'i.claim_id')
            ->whereBetween('v.date_from', ["$yearStart-01-01", "$yearEnd-12-31"]);

        if (!empty($medications)) { $q->whereIn('i.item_id', $medications); }

        $rows = $q->selectRaw("CASE
                WHEN CAST(NULLIF(TRIM(v.ppi_score), '') AS UNSIGNED) > 26 THEN 'High'
                ELSE 'Low' END as econ, SUM(COALESCE(i.qty_provided,1)) as total_usage")
            ->groupBy('econ')
            ->get()
            ->pluck('total_usage', 'econ')
            ->toArray();

        return [ 'labels' => ['Low','High'], 'data' => [ (int)($rows['Low'] ?? 0), (int)($rows['High'] ?? 0) ] ];
    }

    /**
     * Get district distribution
     */
    private function getDistrictDistribution($medications, $yearStart, $yearEnd)
    {
        $q = DB::table('items as i')
            ->join('visit_data as v', 'v.claim_id', '=', 'i.claim_id')
            ->whereBetween('v.date_from', ["$yearStart-01-01", "$yearEnd-12-31"]);

        if (!empty($medications)) { $q->whereIn('i.item_id', $medications); }

        $rows = $q->selectRaw("COALESCE(NULLIF(TRIM(v.district_name), ''), 'Unknown') as name, SUM(COALESCE(i.qty_provided,1)) as total_usage")
            ->groupBy('name')
            ->orderByDesc('total_usage')
            ->get();

        return [ 'labels' => $rows->pluck('name')->toArray(), 'data' => $rows->pluck('total_usage')->toArray() ];
    }



    /**
     * Get heat map data for geographic visualization
     */
    private function getHeatmapData($medications, $yearStart, $yearEnd)
    {
        // Disabled for performance and missing district coordinates
        return [];
    }

    /**
     * Get available medications for filtering
     */
    public function getAvailableMedications(Request $request)
    {
        try {
            $q = trim((string)$request->input('q', ''));
            $limit = (int)($request->input('limit', 200));
            if ($limit <= 0 || $limit > 1000) { $limit = 200; }

            $base = DB::table('items as i')
                ->join('visit_data as v', 'v.claim_id', '=', 'i.claim_id');
            if ($q !== '') {
                $base->where(function($w) use ($q) {
                    $w->where('i.item_name', 'like', "%$q%")
                      ->orWhere(DB::raw('CAST(i.item_id AS CHAR)'), 'like', "%$q%");
                });
            }

            $rows = $base->select('i.item_id as id', DB::raw('MAX(i.item_name) as name'), DB::raw('SUM(COALESCE(i.qty_provided,1)) as total_usage'))
                ->groupBy('i.item_id')
                ->orderByDesc('total_usage')
                ->limit($limit)
                ->get();

            $medications = $rows->map(function ($r) {
                return [ 'id' => (int)$r->id, 'name' => $r->name, 'usage' => (int)$r->total_usage ];
            });

            return response()->json([ 'success' => true, 'medications' => $medications ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch medications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available years for filtering
     */
    public function getAvailableYears()
    {
        try {
            $years = DB::table('visit_data')->select(DB::raw('DISTINCT YEAR(date_from) as year'))
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($years)) {
                $years = [date('Y')];
            }

            return response()->json([ 'success' => true, 'years' => $years ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch years',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Paginated usage table API: sums usage by item_name/item_id for the selected period and optional medication filter
     * Query params: year_start, year_end, medications[] (optional), page, per_page, q (search by name or id)
     */
    public function getUsageTable(Request $request)
    {
        try {
            $medications = $request->input('medications', []);
            if (is_string($medications) && $medications !== '') { $medications = [$medications]; }
            if (!is_array($medications)) { $medications = []; }

            $yearStart = (int)$request->input('year_start', date('Y'));
            $yearEnd = (int)$request->input('year_end', date('Y'));
            $page = max(1, (int)$request->input('page', 1));
            $perPage = (int)$request->input('per_page', 20);
            if ($perPage <= 0 || $perPage > 200) { $perPage = 20; }
            $q = trim((string)$request->input('q', ''));

            $base = DB::table('items as i')
                ->join('visit_data as v', 'v.claim_id', '=', 'i.claim_id')
                ->whereBetween('v.date_from', ["$yearStart-01-01", "$yearEnd-12-31"]);

            if (!empty($medications)) { $base->whereIn('i.item_id', $medications); }
            if ($q !== '') {
                $base->where(function($w) use ($q) {
                    $w->where('i.item_name', 'like', "%$q%")
                      ->orWhere(DB::raw('CAST(i.item_id AS CHAR)'), 'like', "%$q%");
                });
            }

            $countQuery = clone $base;
            $total = $countQuery->select(DB::raw('COUNT(DISTINCT i.item_id) as cnt'))->value('cnt');

            $rows = $base->select('i.item_id as id', DB::raw('MAX(i.item_name) as name'), DB::raw('SUM(COALESCE(i.qty_provided,1)) as total_usage'))
                ->groupBy('i.item_id')
                ->orderByDesc('total_usage')
                ->forPage($page, $perPage)
                ->get();

            return response()->json([
                'success' => true,
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int)$total,
                'rows' => $rows,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch usage table',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
