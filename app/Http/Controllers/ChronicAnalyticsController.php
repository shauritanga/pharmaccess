<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ChronicAnalyticsController extends Controller
{
    /**
     * GET /api/chronic-analytics
     * Filters: year_start, year_end
     * Data source: visit_data + icd_codes (filter to Diabetes/Hypertension by icd_name)
     */
    public function getAnalyticsData(Request $request)
    {
        $yearStart = max(2020, (int) $request->input('year_start', date('Y')));
        $yearEnd   = min(date('Y'), (int) $request->input('year_end', date('Y')));
        if ($yearStart > $yearEnd) { $yearStart = $yearEnd; }

        $groups = $this->getChronicGroupsIcdIds(); // ['Diabetes'=>[...], 'Hypertension'=>[...]]

        $cacheKey = 'chronic_analytics_v2_' . md5(json_encode([$yearStart, $yearEnd, array_map('count', $groups)]));
        $data = Cache::remember($cacheKey, 3600, function () use ($groups, $yearStart, $yearEnd) {
            return [
                'monthly_patients' => $this->getMonthlyPatientsSeries($groups, $yearStart, $yearEnd),
                'age_group_patients' => $this->getAgeGroupPatientsSeries($groups, $yearStart, $yearEnd),
                'economic_status_patients' => $this->getEconomicStatusPatientsSeries($groups, $yearStart, $yearEnd),
            ];
        });

        return response()->json([
            'success' => true,
            'filters' => [ 'year_range' => [$yearStart, $yearEnd] ],
            'charts' => $data,
        ]);
    }

    /**
     * GET /api/chronic-analytics/available-years
     */
    public function getAvailableYears()
    {
        $years = DB::table('visit_data')->selectRaw('DISTINCT YEAR(date_from) as year')
            ->orderBy('year')
            ->pluck('year')
            ->toArray();
        return response()->json(['success' => true, 'years' => $years]);
    }

    /**
     * Chronic ICD IDs grouped: Diabetes and Hypertension using icd_name matching
     */
    private function getChronicGroupsIcdIds(): array
    {
        $diabetes = DB::table('icd_codes')
            ->whereRaw('LOWER(icd_name) LIKE ?', ['%diabet%'])
            ->pluck('icd_id')->map(fn($v)=>(int)$v)->toArray();
        $hypertension = DB::table('icd_codes')
            ->whereRaw('LOWER(icd_name) LIKE ?', ['%hyperten%'])
            ->pluck('icd_id')->map(fn($v)=>(int)$v)->toArray();
        return [ 'Diabetes' => $diabetes, 'Hypertension' => $hypertension ];
    }

    /**
     * Monthly patients per disease group (series): DISTINCT insuree_id per month, accumulated over years
     */
    private function getMonthlyPatientsSeries(array $groups, int $yearStart, int $yearEnd): array
    {
        $start = Carbon::create($yearStart, 1, 1)->startOfDay();
        $end   = Carbon::create($yearEnd, 12, 31)->endOfDay();
        $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $series = [];
        foreach ($groups as $name => $ids) {
            if (empty($ids)) { $series[] = ['name'=>$name,'data'=>array_fill(0,12,0)]; continue; }
            $rows = DB::table('visit_data as v')
                ->whereBetween('v.date_from', [$start, $end])
                ->whereIn('v.icd_id', $ids)
                ->selectRaw('MONTH(v.date_from) as m, COUNT(DISTINCT v.insuree_id) as patients')
                ->groupBy('m')
                ->orderBy('m')
                ->get()
                ->pluck('patients', 'm')
                ->toArray();
            $data = [];
            for ($i=1; $i<=12; $i++) { $data[] = (int)($rows[$i] ?? 0); }
            $series[] = [ 'name' => $name, 'data' => $data ];
        }
        return ['labels'=>$labels, 'series'=>$series];
    }

    /**
     * Age group patients per disease group (series)
     */
    private function getAgeGroupPatientsSeries(array $groups, int $yearStart, int $yearEnd): array
    {
        $start = Carbon::create($yearStart, 1, 1)->startOfDay();
        $end   = Carbon::create($yearEnd, 12, 31)->endOfDay();
        $labels = ['0-5','6-17','18-35','36-55','56+'];
        $series = [];
        foreach ($groups as $name => $ids) {
            if (empty($ids)) { $series[] = ['name'=>$name,'data'=>array_fill(0,count($labels),0)]; continue; }
            $sub = DB::table('visit_data as v')
                ->whereBetween('v.date_from', [$start, $end])
                ->whereIn('v.icd_id', $ids)
                ->whereNotNull('v.dob')
                ->selectRaw("v.insuree_id as pid, CASE\n                    WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) BETWEEN 0 AND 5 THEN '0-5'\n                    WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) BETWEEN 6 AND 17 THEN '6-17'\n                    WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) BETWEEN 18 AND 35 THEN '18-35'\n                    WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) BETWEEN 36 AND 55 THEN '36-55'\n                    WHEN TIMESTAMPDIFF(YEAR, v.dob, v.date_from) >= 56 THEN '56+'\n                    ELSE 'Unknown' END as grp")
                ->distinct();

            $rows = DB::query()->fromSub($sub, 't')
                ->selectRaw('t.grp, COUNT(DISTINCT t.pid) as patients')
                ->groupBy('t.grp')
                ->get()
                ->pluck('patients', 'grp')
                ->toArray();

            $data = [];
            foreach ($labels as $g) { $data[] = (int)($rows[$g] ?? 0); }
            $series[] = ['name' => $name, 'data' => $data];
        }
        return ['labels'=>$labels, 'series'=>$series];
    }

    /**
     * Economic status patients per disease group (series): classify by max PPI per patient across visits in range
     * Rule: ppi_score > 26 => High, else Low
     */
    private function getEconomicStatusPatientsSeries(array $groups, int $yearStart, int $yearEnd): array
    {
        $start = Carbon::create($yearStart, 1, 1)->startOfDay();
        $end   = Carbon::create($yearEnd, 12, 31)->endOfDay();
        $labels = ['Low','High'];
        $series = [];
        foreach ($groups as $name => $ids) {
            if (empty($ids)) { $series[] = ['name'=>$name,'data'=>[0,0]]; continue; }
            $sub = DB::table('visit_data as v')
                ->whereBetween('v.date_from', [$start, $end])
                ->whereIn('v.icd_id', $ids)
                ->selectRaw("v.insuree_id as pid, MAX(CAST(NULLIF(TRIM(v.ppi_score), '') AS UNSIGNED)) as max_ppi")
                ->groupBy('v.insuree_id');

            $rows = DB::query()->fromSub($sub, 't')
                ->selectRaw("CASE WHEN COALESCE(t.max_ppi, 0) > 26 THEN 'High' ELSE 'Low' END as econ, COUNT(*) as patients")
                ->groupBy('econ')
                ->get()
                ->pluck('patients', 'econ')
                ->toArray();

            $series[] = ['name'=>$name, 'data'=>[ (int)($rows['Low'] ?? 0), (int)($rows['High'] ?? 0) ]];
        }
        return ['labels'=>$labels, 'series'=>$series];
    }
}

