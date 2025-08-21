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

        // Filter out invalid disease IDs
        if (!empty($diseases)) {
            $validDiseaseIds = Disease::pluck('id')->toArray();
            $diseases = array_intersect($diseases, $validDiseaseIds);
        }

        // Create cache key based on filters
        $cacheKey = "disease_analytics_" . md5(serialize([
            'diseases' => $diseases,
            'year_start' => $yearStart,
            'year_end' => $yearEnd
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
     * Get monthly distribution of cases
     */
    private function getMonthlyDistribution($diseases, $yearStart, $yearEnd)
    {
        $query = DiseaseCase::query()
            ->byYearRange($yearStart, $yearEnd);

        if (!empty($diseases)) {
            $query->whereIn('disease_id', $diseases);
        }

        $monthlyData = $query
            ->select(
                DB::raw('MONTH(reported_date) as month'),
                DB::raw('COUNT(*) as cases')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('cases', 'month')
            ->toArray();

        // Fill missing months with 0
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $data = [];

        for ($i = 1; $i <= 12; $i++) {
            $data[] = $monthlyData[$i] ?? 0;
        }

        return [
            'labels' => $months,
            'data' => $data
        ];
    }

    /**
     * Get gender distribution of cases
     */
    private function getGenderDistribution($diseases, $yearStart, $yearEnd)
    {
        $query = DiseaseCase::query()
            ->join('patients', 'disease_cases.patient_id', '=', 'patients.id')
            ->byYearRange($yearStart, $yearEnd);

        if (!empty($diseases)) {
            $query->whereIn('disease_id', $diseases);
        }

        $genderData = $query
            ->select('patients.gender', DB::raw('COUNT(*) as cases'))
            ->whereIn('patients.gender', ['male', 'female']) // Only include male and female
            ->groupBy('patients.gender')
            ->get()
            ->pluck('cases', 'gender')
            ->toArray();

        return [
            'labels' => ['Male', 'Female'],
            'data' => [
                $genderData['male'] ?? 0,
                $genderData['female'] ?? 0
            ]
        ];
    }

    /**
     * Get age group distribution of cases
     */
    private function getAgeGroupDistribution($diseases, $yearStart, $yearEnd)
    {
        $query = DiseaseCase::query()
            ->join('patients', 'disease_cases.patient_id', '=', 'patients.id')
            ->byYearRange($yearStart, $yearEnd);

        if (!empty($diseases)) {
            $query->whereIn('disease_id', $diseases);
        }

        $ageGroupData = $query
            ->select('patients.age_group', DB::raw('COUNT(*) as cases'))
            ->groupBy('patients.age_group')
            ->get()
            ->pluck('cases', 'age_group')
            ->toArray();

        $ageGroups = ['0-5', '6-17', '18-35', '36-55', '56+'];
        $data = [];

        foreach ($ageGroups as $group) {
            $data[] = $ageGroupData[$group] ?? 0;
        }

        return [
            'labels' => $ageGroups,
            'data' => $data
        ];
    }

    /**
     * Get economic status distribution of cases
     */
    private function getEconomicStatusDistribution($diseases, $yearStart, $yearEnd)
    {
        $query = DiseaseCase::query()
            ->join('patients', 'disease_cases.patient_id', '=', 'patients.id')
            ->byYearRange($yearStart, $yearEnd);

        if (!empty($diseases)) {
            $query->whereIn('disease_id', $diseases);
        }

        $economicData = $query
            ->select('patients.economic_status', DB::raw('COUNT(*) as cases'))
            ->groupBy('patients.economic_status')
            ->get()
            ->pluck('cases', 'economic_status')
            ->toArray();

        return [
            'labels' => ['Low', 'Middle', 'High'],
            'data' => [
                $economicData['low'] ?? 0,
                $economicData['middle'] ?? 0,
                $economicData['high'] ?? 0
            ]
        ];
    }

    /**
     * Get district distribution of cases
     */
    private function getDistrictDistribution($diseases, $yearStart, $yearEnd)
    {
        $query = DiseaseCase::query()
            ->join('patients', 'disease_cases.patient_id', '=', 'patients.id')
            ->join('districts', 'patients.district_id', '=', 'districts.id')
            ->byYearRange($yearStart, $yearEnd);

        if (!empty($diseases)) {
            $query->whereIn('disease_id', $diseases);
        }

        $districtData = $query
            ->select('districts.name', DB::raw('COUNT(*) as cases'))
            ->groupBy('districts.name')
            ->orderBy('cases', 'desc')
            ->get();

        return [
            'labels' => $districtData->pluck('name')->toArray(),
            'data' => $districtData->pluck('cases')->toArray()
        ];
    }

    /**
     * Get heatmap data with both absolute and per capita options
     */
    private function getHeatmapData($diseases, $yearStart, $yearEnd)
    {
        $query = DiseaseCase::query()
            ->join('patients', 'disease_cases.patient_id', '=', 'patients.id')
            ->join('districts', 'patients.district_id', '=', 'districts.id')
            ->byYearRange($yearStart, $yearEnd);

        if (!empty($diseases)) {
            $query->whereIn('disease_id', $diseases);
        }

        $heatmapData = $query
            ->select(
                'districts.name',
                'districts.latitude',
                'districts.longitude',
                'districts.population',
                DB::raw('COUNT(*) as cases')
            )
            ->groupBy('districts.id', 'districts.name', 'districts.latitude', 'districts.longitude', 'districts.population')
            ->get();

        return $heatmapData->map(function ($item) {
            $casesPerCapita = $item->population > 0
                ? round(($item->cases / $item->population) * 100000, 2)
                : 0;

            return [
                'district' => $item->name,
                'lat' => (float) $item->latitude,
                'lng' => (float) $item->longitude,
                'cases' => $item->cases,
                'population' => $item->population,
                'cases_per_capita' => $casesPerCapita
            ];
        })->toArray();
    }

    /**
     * Get available diseases for filter dropdown
     */
    public function getAvailableDiseases()
    {
        $diseases = Disease::select('id', 'name', 'category')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'diseases' => $diseases
        ]);
    }

    /**
     * Get available years for filter dropdown
     */
    public function getAvailableYears()
    {
        $years = DiseaseCase::selectRaw('YEAR(reported_date) as year')
            ->distinct()
            ->orderBy('year')
            ->pluck('year')
            ->toArray();

        return response()->json([
            'success' => true,
            'years' => $years
        ]);
    }
}
