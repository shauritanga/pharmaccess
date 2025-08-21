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
        $query = Prescription::query()
            ->byYearRange($yearStart, $yearEnd);
            
        if (!empty($medications)) {
            $query->whereIn('medication_id', $medications);
        }
        
        $monthlyData = $query
            ->select(
                DB::raw('YEAR(prescribed_date) as year'),
                DB::raw('MONTH(prescribed_date) as month'),
                DB::raw('COUNT(*) as prescriptions')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
            
        // Create labels and data arrays for line chart
        $labels = [];
        $data = [];
        
        for ($year = $yearStart; $year <= $yearEnd; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $monthName = date('M Y', mktime(0, 0, 0, $month, 1, $year));
                $labels[] = $monthName;
                
                $prescriptionCount = $monthlyData
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();
                    
                $data[] = $prescriptionCount ? $prescriptionCount->prescriptions : 0;
            }
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get gender distribution of patients receiving prescriptions
     */
    private function getGenderDistribution($medications, $yearStart, $yearEnd)
    {
        $query = Prescription::query()
            ->join('patients', 'prescriptions.patient_id', '=', 'patients.id')
            ->byYearRange($yearStart, $yearEnd);
            
        if (!empty($medications)) {
            $query->whereIn('medication_id', $medications);
        }
        
        $genderData = $query
            ->select('patients.gender', DB::raw('COUNT(DISTINCT prescriptions.id) as prescriptions'))
            ->whereIn('patients.gender', ['male', 'female']) // Only include male and female
            ->groupBy('patients.gender')
            ->get()
            ->pluck('prescriptions', 'gender')
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
     * Get age group distribution
     */
    private function getAgeGroupDistribution($medications, $yearStart, $yearEnd)
    {
        $query = Prescription::query()
            ->join('patients', 'prescriptions.patient_id', '=', 'patients.id')
            ->byYearRange($yearStart, $yearEnd);
            
        if (!empty($medications)) {
            $query->whereIn('medication_id', $medications);
        }
        
        $ageData = $query
            ->select('patients.age_group', DB::raw('COUNT(DISTINCT prescriptions.id) as prescriptions'))
            ->groupBy('patients.age_group')
            ->orderByRaw("FIELD(patients.age_group, '0-5', '6-17', '18-35', '36-55', '56+')")
            ->get();
            
        return [
            'labels' => $ageData->pluck('age_group')->toArray(),
            'data' => $ageData->pluck('prescriptions')->toArray()
        ];
    }

    /**
     * Get economic status distribution
     */
    private function getEconomicStatusDistribution($medications, $yearStart, $yearEnd)
    {
        $query = Prescription::query()
            ->join('patients', 'prescriptions.patient_id', '=', 'patients.id')
            ->byYearRange($yearStart, $yearEnd);
            
        if (!empty($medications)) {
            $query->whereIn('medication_id', $medications);
        }
        
        $economicData = $query
            ->select('patients.economic_status', DB::raw('COUNT(DISTINCT prescriptions.id) as prescriptions'))
            ->groupBy('patients.economic_status')
            ->orderByRaw("FIELD(patients.economic_status, 'low', 'middle', 'high')")
            ->get();
            
        return [
            'labels' => array_map('ucfirst', $economicData->pluck('economic_status')->toArray()),
            'data' => $economicData->pluck('prescriptions')->toArray()
        ];
    }

    /**
     * Get district distribution
     */
    private function getDistrictDistribution($medications, $yearStart, $yearEnd)
    {
        $query = Prescription::query()
            ->join('patients', 'prescriptions.patient_id', '=', 'patients.id')
            ->join('districts', 'patients.district_id', '=', 'districts.id')
            ->byYearRange($yearStart, $yearEnd);
            
        if (!empty($medications)) {
            $query->whereIn('medication_id', $medications);
        }
        
        $districtData = $query
            ->select('districts.name', DB::raw('COUNT(DISTINCT prescriptions.id) as prescriptions'))
            ->groupBy('districts.name')
            ->orderBy('prescriptions', 'desc')
            ->get();
            
        return [
            'labels' => $districtData->pluck('name')->toArray(),
            'data' => $districtData->pluck('prescriptions')->toArray()
        ];
    }



    /**
     * Get heat map data for geographic visualization
     */
    private function getHeatmapData($medications, $yearStart, $yearEnd)
    {
        $query = Prescription::query()
            ->join('patients', 'prescriptions.patient_id', '=', 'patients.id')
            ->join('districts', 'patients.district_id', '=', 'districts.id')
            ->byYearRange($yearStart, $yearEnd);
            
        if (!empty($medications)) {
            $query->whereIn('medication_id', $medications);
        }
        
        $heatmapData = $query
            ->select(
                'districts.name as district',
                'districts.latitude as lat',
                'districts.longitude as lng',
                'districts.population',
                DB::raw('COUNT(DISTINCT prescriptions.id) as prescriptions')
            )
            ->groupBy('districts.id', 'districts.name', 'districts.latitude', 'districts.longitude', 'districts.population')
            ->get()
            ->map(function ($item) {
                return [
                    'district' => $item->district,
                    'lat' => (float) $item->lat,
                    'lng' => (float) $item->lng,
                    'population' => $item->population,
                    'prescriptions' => $item->prescriptions,
                    'prescriptions_per_capita' => $item->population > 0 
                        ? round(($item->prescriptions / $item->population) * 100000, 2) 
                        : 0
                ];
            });
            
        return $heatmapData->toArray();
    }

    /**
     * Get available medications for filtering
     */
    public function getAvailableMedications()
    {
        try {
            $medications = Medication::select('id', 'name', 'category')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'medications' => $medications
            ]);

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
            $years = Prescription::select(DB::raw('DISTINCT YEAR(prescribed_date) as year'))
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($years)) {
                $years = [date('Y')]; // Default to current year if no data
            }

            return response()->json([
                'success' => true,
                'years' => $years
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch years',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
