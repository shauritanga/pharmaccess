<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DiseaseCase extends Model
{
    protected $fillable = [
        'disease_id',
        'patient_id',
        'reported_date',
        'status',
        'severity',
    ];

    protected $casts = [
        'reported_date' => 'date',
        'status' => 'string',
        'severity' => 'string',
    ];

    /**
     * Get the disease this case belongs to
     */
    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }

    /**
     * Get the patient this case belongs to
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Scope to filter by disease
     */
    public function scopeByDisease($query, $diseaseId)
    {
        return $query->where('disease_id', $diseaseId);
    }

    /**
     * Scope to filter by year
     */
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('reported_date', $year);
    }

    /**
     * Scope to filter by year range
     */
    public function scopeByYearRange($query, $startYear, $endYear)
    {
        return $query->whereBetween('reported_date', [
            Carbon::createFromDate($startYear, 1, 1),
            Carbon::createFromDate($endYear, 12, 31)
        ]);
    }

    /**
     * Scope to filter by month
     */
    public function scopeByMonth($query, $month, $year = null)
    {
        $query = $query->whereMonth('reported_date', $month);

        if ($year) {
            $query = $query->whereYear('reported_date', $year);
        }

        return $query;
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get active cases
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get recovered cases
     */
    public function scopeRecovered($query)
    {
        return $query->where('status', 'recovered');
    }
}
