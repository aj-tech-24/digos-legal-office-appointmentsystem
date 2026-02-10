<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'narrative_hash',
        'original_narrative',
        'professional_summary',
        'detected_services',
        'complexity_level',
        'estimated_duration_minutes',
        'document_checklist',
        'raw_ai_response',
    ];

    protected $casts = [
        'detected_services' => 'array',
        'document_checklist' => 'array',
        'raw_ai_response' => 'array',
        'estimated_duration_minutes' => 'integer',
    ];

    /**
     * Complexity level durations in minutes
     */
    public const COMPLEXITY_DURATIONS = [
        'simple' => 45,
        'moderate' => 75,
        'complex' => 120,
    ];

    /**
     * Get the recommendation items (lawyer scores).
     */
    public function items(): HasMany
    {
        return $this->hasMany(AiRecommendationItem::class)->orderBy('rank');
    }

    /**
     * Get the appointments using this recommendation.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Generate a hash for the narrative
     */
    public static function generateHash(string $narrative, array $services = []): string
    {
        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $narrative)));
        $servicesString = implode(',', array_map('strtolower', $services));
        
        return hash('sha256', $normalized . '|' . $servicesString);
    }

    /**
     * Find existing recommendation by narrative
     */
    public static function findByNarrative(string $narrative, array $services = []): ?self
    {
        $hash = self::generateHash($narrative, $services);
        
        return static::where('narrative_hash', $hash)->first();
    }

    /**
     * Get the estimated duration based on complexity
     */
    public function getEstimatedDuration(): int
    {
        return $this->estimated_duration_minutes 
            ?? self::COMPLEXITY_DURATIONS[$this->complexity_level] 
            ?? 60;
    }

    /**
     * Get primary service type
     */
    public function getPrimaryServiceAttribute(): ?string
    {
        $services = $this->detected_services ?? [];
        return $services['primary'] ?? ($services[0] ?? null);
    }

    /**
     * Get secondary service type
     */
    public function getSecondaryServiceAttribute(): ?string
    {
        $services = $this->detected_services ?? [];
        return $services['secondary'] ?? ($services[1] ?? null);
    }

    /**
     * Get complexity label
     */
    public function getComplexityLabelAttribute(): string
    {
        return ucfirst($this->complexity_level);
    }
}
