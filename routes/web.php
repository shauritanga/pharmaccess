<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\DiseaseAnalyticsController;


Route::get('/',[FrontController::class,'index'])->name('home');

// Dashboard API
Route::get('/api/dashboard', [FrontController::class, 'getDashboardData'])->name('api.dashboard');


Route::get('/hospitals', [FrontController::class,'hospitals'])->name('hospitals');

Route::get('/add-hospitals', [FrontController::class,'Addhospitals'])->name('add-hospitals');

Route::get('/edit-hospitals', [FrontController::class,'Edithospitals'])->name('edit-hospitals');

Route::get('/medication', [FrontController::class,'medication'])->name('medication');

Route::get('/top-diseases', [FrontController::class,'showTopDiseases'])->name('top-diseases');

Route::get('/settings', [FrontController::class, 'settings'])->name('settings');

Route::get('/patients', [FrontController::class, 'patients'])->name('patients');

Route::get('/chronic-diseases', [FrontController::class, 'showChronicDiseases'])->name('chronic-diseases');

// Disease Analytics API Routes
Route::prefix('api/diseases')->group(function () {
    Route::get('/analytics-data', [DiseaseAnalyticsController::class, 'getAnalyticsData'])->name('api.diseases.analytics');
    Route::get('/available-diseases', [DiseaseAnalyticsController::class, 'getAvailableDiseases'])->name('api.diseases.available');
    Route::get('/available-years', [DiseaseAnalyticsController::class, 'getAvailableYears'])->name('api.diseases.years');
});

// Medication Analytics API Routes
Route::prefix('api/medication-analytics')->group(function () {
    Route::get('/', [\App\Http\Controllers\MedicationAnalyticsController::class, 'getAnalyticsData'])->name('api.medication.analytics');
    Route::get('/available-medications', [\App\Http\Controllers\MedicationAnalyticsController::class, 'getAvailableMedications'])->name('api.medication.available');
    Route::get('/available-years', [\App\Http\Controllers\MedicationAnalyticsController::class, 'getAvailableYears'])->name('api.medication.years');
});

// Test route to verify data
Route::get('/test-data', function () {
    $diseases = \App\Models\Disease::count();
    $districts = \App\Models\District::count();
    $patients = \App\Models\Patient::count();
    $cases = \App\Models\DiseaseCase::count();

    return response()->json([
        'diseases' => $diseases,
        'districts' => $districts,
        'patients' => $patients,
        'disease_cases' => $cases,
        'sample_disease' => \App\Models\Disease::first(),
        'sample_district' => \App\Models\District::first(),
    ]);
});

// For redirecting to a disease detail page


