<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index() {
        return view('frontend.index');
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

    public function medication(Request $request)
{
    $selectedYear = $request->input('year', date('Y'));
    $availableYears = range(2021, 2025);

    $months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    $medications = [
        'Paracetamol', 'Amoxicillin', 'Insulin', 'Ibuprofen', 'Aspirin',
        'Metformin', 'Omeprazole', 'Cetirizine', 'Lisinopril', 'Azithromycin',
        'Simvastatin', 'Ciprofloxacin', 'Metronidazole', 'Atorvastatin', 'Losartan',
        'Hydrochlorothiazide', 'Levothyroxine', 'Doxycycline', 'Amlodipine', 'Prednisone',
        'Gabapentin', 'Albuterol', 'Tramadol', 'Clopidogrel', 'Diazepam',
        'Morphine', 'Fluoxetine', 'Salbutamol', 'Naproxen', 'Ranitidine',
        'Penicillin', 'Furosemide', 'Amiodarone', 'Warfarin', 'Tamsulosin',
        'Spironolactone', 'Codeine', 'Erythromycin', 'Clindamycin', 'Hydroxychloroquine',
        'Insulin Glargine', 'Insulin Aspart', 'Insulin Lispro', 'Bisoprolol', 'Enalapril',
        'Carvedilol', 'Digoxin', 'Ketoconazole', 'Lamivudine', 'Nevirapine'
    ];

    $medicationData = [];

    foreach ($medications as $medicine) {
        $medicationData[$medicine] = [];
        foreach ($months as $month) {
            $medicationData[$medicine][] = rand(100, 1000);
        }
    }

    return view('frontend.medication', compact('months', 'medications', 'medicationData', 'availableYears', 'selectedYear'));
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
    $diseases = [
        ['name' => 'Malaria', 'cases' => 120],
        ['name' => 'Typhoid', 'cases' => 95],
        ['name' => 'Cholera', 'cases' => 75],
        ['name' => 'Dengue', 'cases' => 60],
        ['name' => 'Flu', 'cases' => 45],
    ];

    return view('frontend.top-diseases', compact('diseases'));
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
