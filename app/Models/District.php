<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'population',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'population' => 'integer',
    ];

    /**
     * Get all patients in this district
     */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Get disease cases in this district through patients
     */
    public function diseaseCases()
    {
        return $this->hasManyThrough(DiseaseCase::class, Patient::class);
    }

    /**
     * Calculate cases per capita for this district
     */
    public function getCasesPerCapitaAttribute()
    {
        if (!$this->population || $this->population == 0) {
            return 0;
        }

        $totalCases = $this->diseaseCases()->count();
        return round(($totalCases / $this->population) * 100000, 2); // Per 100,000 population
    }

    /**
     * Scope to search by name
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }
}
