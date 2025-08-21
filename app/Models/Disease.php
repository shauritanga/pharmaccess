<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disease extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
    ];

    protected $casts = [
        'category' => 'string',
    ];

    /**
     * Get all disease cases for this disease
     */
    public function diseaseCases(): HasMany
    {
        return $this->hasMany(DiseaseCase::class);
    }

    /**
     * Get patients affected by this disease through disease cases
     */
    public function patients()
    {
        return $this->hasManyThrough(Patient::class, DiseaseCase::class);
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
}
