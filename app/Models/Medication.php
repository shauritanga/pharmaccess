<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medication extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'dosage_form',
        'strength',
        'manufacturer',
    ];

    protected $casts = [
        'category' => 'string',
        'dosage_form' => 'string',
        'strength' => 'string',
    ];

    /**
     * Get all prescriptions for this medication
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get patients who have been prescribed this medication
     */
    public function patients()
    {
        return $this->hasManyThrough(Patient::class, Prescription::class);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to search by name
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }

    /**
     * Get total prescriptions count
     */
    public function getTotalPrescriptionsAttribute()
    {
        return $this->prescriptions()->count();
    }
}
