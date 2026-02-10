<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the lawyers that have this specialization.
     */
    public function lawyers(): BelongsToMany
    {
        return $this->belongsToMany(Lawyer::class, 'lawyer_specializations')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Scope to get only active specializations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
