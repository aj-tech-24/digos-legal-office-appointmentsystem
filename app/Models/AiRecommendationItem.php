<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_recommendation_id',
        'lawyer_id',
        'match_score',
        'score_breakdown',
        'rank',
    ];

    protected $casts = [
        'match_score' => 'decimal:2',
        'score_breakdown' => 'array',
        'rank' => 'integer',
    ];

    /**
     * Score factor weights
     */
    public const SCORE_WEIGHTS = [
        'specialization_match' => 40,
        'similar_cases_handled' => 15,
        'availability_match' => 15,
        'experience' => 10,
        'language_match' => 10,
        'current_workload' => 10,
    ];

    /**
     * Score factor labels for display
     */
    public const SCORE_LABELS = [
        'specialization_match' => 'Specialization Match',
        'similar_cases_handled' => 'Similar Cases Handled',
        'availability_match' => 'Availability',
        'experience' => 'Experience',
        'language_match' => 'Language Match',
        'current_workload' => 'Current Workload',
    ];

    /**
     * Get the AI recommendation.
     */
    public function aiRecommendation(): BelongsTo
    {
        return $this->belongsTo(AiRecommendation::class);
    }

    /**
     * Get the lawyer.
     */
    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }

    /**
     * Get formatted score breakdown for display
     */
    public function getFormattedBreakdownAttribute(): array
    {
        $breakdown = $this->score_breakdown ?? [];
        $formatted = [];

        foreach (self::SCORE_WEIGHTS as $key => $maxWeight) {
            $score = $breakdown[$key] ?? 0;
            $formatted[] = [
                'key' => $key,
                'label' => self::SCORE_LABELS[$key],
                'score' => $score,
                'max' => $maxWeight,
                'percentage' => $maxWeight > 0 ? round(($score / $maxWeight) * 100) : 0,
            ];
        }

        return $formatted;
    }

    /**
     * Get match score as percentage string
     */
    public function getMatchPercentageAttribute(): string
    {
        return number_format($this->match_score, 0) . '%';
    }
}
