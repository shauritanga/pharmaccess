<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontController;


Route::get('/',[FrontController::class,'index'])->name('home');


Route::get('/hospitals', [FrontController::class,'hospitals'])->name('hospitals');

Route::get('/add-hospitals', [FrontController::class,'Addhospitals'])->name('add-hospitals');

Route::get('/edit-hospitals', [FrontController::class,'Edithospitals'])->name('edit-hospitals');

Route::get('/medication', [FrontController::class,'medication'])->name('medication');

Route::get('/top-diseases', [FrontController::class,'showTopDiseases'])->name('top-diseases');

Route::get('/settings', [FrontController::class, 'settings'])->name('settings');

Route::get('/patients', [FrontController::class, 'patients'])->name('patients');

Route::get('/chronic-diseases', [FrontController::class, 'showChronicDiseases'])->name('chronic-diseases');

// For redirecting to a disease detail page


