<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $fillable = [
        'gender',
        'age',
        'age_group',
        'economic_status',
        'district_id',
    ];

    protected $casts = [
        'age' => 'integer',
        'gender' => 'string',
        'age_group' => 'string',
        'economic_status' => 'string',
    ];

    /**
     * Get the district this patient belongs to
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get all disease cases for this patient
     */
    public function diseaseCases(): HasMany
    {
        return $this->hasMany(DiseaseCase::class);
    }

    /**
     * Get all prescriptions for this patient
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get diseases this patient has had through disease cases
     */
    public function diseases()
    {
        return $this->hasManyThrough(Disease::class, DiseaseCase::class);
    }

    /**
     * Automatically calculate age group when age is set
     */
    public function setAgeAttribute($value)
    {
        $this->attributes['age'] = $value;
        $this->attributes['age_group'] = $this->calculateAgeGroup($value);
    }

    /**
     * Calculate age group based on age
     */
    private function calculateAgeGroup($age)
    {
        if ($age <= 5) return '0-5';
        if ($age <= 17) return '6-17';
        if ($age <= 35) return '18-35';
        if ($age <= 55) return '36-55';
        return '56+';
    }

    /**
     * Scope to filter by gender
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope to filter by age group
     */
    public function scopeByAgeGroup($query, $ageGroup)
    {
        return $query->where('age_group', $ageGroup);
    }

    /**
     * Scope to filter by economic status
     */
    public function scopeByEconomicStatus($query, $status)
    {
        return $query->where('economic_status', $status);
    }
}
