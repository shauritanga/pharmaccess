<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\DiseaseAnalyticsController;
// Serve GeoJSON via Laravel (handles shared hosting docroots)
Route::get('/data/zanibar_kata2.geojson', function () {
    $path = public_path('data/zanibar_kata2.geojson');
    if (!file_exists($path)) {
        abort(404);
    }
    return response(file_get_contents($path), 200, [
        'Content-Type' => 'application/geo+json',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->name('geojson.shehia');



// Auth routes
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Protected app routes: require login, then land on dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/', [FrontController::class,'index'])->name('home');

    // Dashboard API
    Route::get('/api/dashboard', [FrontController::class, 'getDashboardData'])->name('api.dashboard');

    // Health Facilities
    Route::get('/health-facilities', [FrontController::class,'hospitals'])->name('health-facilities');
    // Backward-compat route name alias for legacy links
    Route::get('/hospitals', function() {
        return redirect()->route('health-facilities');
    })->name('hospitals');

    Route::get('/medication', [FrontController::class,'medication'])->name('medication');
    Route::get('/top-diseases', [FrontController::class,'showTopDiseases'])->name('top-diseases');
// Chronic diseases page
Route::get('/chronic', [\App\Http\Controllers\FrontController::class, 'showChronicDiseases'])->name('chronic');
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/password', [\App\Http\Controllers\SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::get('/patients', [FrontController::class, 'patients'])->name('patients');
    Route::get('/chronic-diseases', [FrontController::class, 'showChronicDiseases'])->name('chronic-diseases');

    // Admin - Users management
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/users', [\App\Http\Controllers\Admin\UsersController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [\App\Http\Controllers\Admin\UsersController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [\App\Http\Controllers\Admin\UsersController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\Admin\UsersController::class, 'edit'])->name('admin.users.edit');
        Route::post('/admin/users/{user}', [\App\Http\Controllers\Admin\UsersController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\UsersController::class, 'destroy'])->name('admin.users.destroy');
    });
});

// Disease Analytics API Routes (public or can be moved under auth if needed)
Route::prefix('api/diseases')->group(function () {
    Route::get('/analytics-data', [DiseaseAnalyticsController::class, 'getAnalyticsData'])->name('api.diseases.analytics');
    Route::get('/available-diseases', [DiseaseAnalyticsController::class, 'getAvailableDiseases'])->name('api.diseases.available');
    Route::get('/available-years', [DiseaseAnalyticsController::class, 'getAvailableYears'])->name('api.diseases.years');
});

// Medication Analytics API Routes (public)
Route::prefix('api/medication-analytics')->group(function () {
    Route::get('/', [\App\Http\Controllers\MedicationAnalyticsController::class, 'getAnalyticsData'])->name('api.medication.analytics');
    Route::get('/available-medications', [\App\Http\Controllers\MedicationAnalyticsController::class, 'getAvailableMedications'])->name('api.medication.available');
    Route::get('/available-years', [\App\Http\Controllers\MedicationAnalyticsController::class, 'getAvailableYears'])->name('api.medication.years');
    Route::get('/usage-table', [\App\Http\Controllers\MedicationAnalyticsController::class, 'getUsageTable'])->name('api.medication.usage_table');
});

// Facilities API (list + attendance monthly) (public)
// Chronic diseases analytics API
Route::prefix('api/chronic-analytics')->group(function () {
    Route::get('/', [\App\Http\Controllers\ChronicAnalyticsController::class, 'getAnalyticsData'])->name('api.chronic.analytics');
    Route::get('/available-years', [\App\Http\Controllers\ChronicAnalyticsController::class, 'getAvailableYears'])->name('api.chronic.years');
});
Route::prefix('api/facilities')->group(function () {
    Route::get('/list', [FrontController::class, 'getFacilities'])->name('api.facilities.list');
    Route::get('/attendance', [FrontController::class, 'getFacilityAttendance'])->name('api.facilities.attendance');
});


// Shehia analytics API (cases + medications per shehia) (public)
Route::prefix('api/shehia')->group(function () {
    Route::get('/stats', [FrontController::class, 'getShehiaStats'])->name('api.shehia.stats');
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
