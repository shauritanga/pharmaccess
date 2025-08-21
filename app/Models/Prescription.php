<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    protected $fillable = [
        'medication_id',
        'patient_id',
        'prescribed_date',
        'quantity',
        'dosage',
        'duration_days',
        'status',
        'prescribed_by',
    ];

    protected $casts = [
        'prescribed_date' => 'date',
        'quantity' => 'integer',
        'duration_days' => 'integer',
        'status' => 'string',
    ];

    /**
     * Get the medication this prescription belongs to
     */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Get the patient this prescription belongs to
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Scope to filter by medication
     */
    public function scopeByMedication($query, $medicationId)
    {
        return $query->where('medication_id', $medicationId);
    }

    /**
     * Scope to filter by year
     */
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('prescribed_date', $year);
    }

    /**
     * Scope to filter by year range
     */
    public function scopeByYearRange($query, $startYear, $endYear)
    {
        return $query->whereYear('prescribed_date', '>=', $startYear)
                    ->whereYear('prescribed_date', '<=', $endYear);
    }

    /**
     * Scope to filter by month
     */
    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('prescribed_date', $year)
                    ->whereMonth('prescribed_date', $month);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
