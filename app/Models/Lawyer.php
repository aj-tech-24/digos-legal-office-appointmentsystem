<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lawyer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_number',
        'bio',
        'description',
        'years_of_experience',
        'languages',
        'status',
        'approved_at',
        'approved_by',
        'max_daily_appointments',
        'default_consultation_duration',
    ];

    protected $casts = [
        'languages' => 'array',
        'approved_at' => 'datetime',
        'years_of_experience' => 'integer',
        'max_daily_appointments' => 'integer',
        'default_consultation_duration' => 'integer',
    ];

    /**
     * Get the user that owns the lawyer profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the specializations for the lawyer.
     */
    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, 'lawyer_specializations')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Get the primary specialization.
     */
    public function primarySpecialization()
    {
        return $this->specializations()->wherePivot('is_primary', true)->first();
    }

    /**
     * Get the schedules for the lawyer.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(LawyerSchedule::class);
    }

    /**
     * Get the appointments for the lawyer.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the AI recommendation items for the lawyer.
     */
    public function aiRecommendationItems(): HasMany
    {
        return $this->hasMany(AiRecommendationItem::class);
    }

    /**
     * Get the user who approved this lawyer.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope to get only approved lawyers
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get pending lawyers
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if lawyer is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Get current workload (appointments today)
     */
    public function getCurrentWorkload(): int
    {
        return $this->appointments()
            ->whereDate('start_datetime', today())
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();
    }

    /**
     * Check availability for a specific datetime range
     */
    public function isAvailable(\DateTime $start, \DateTime $end): bool
    {
        // Check if within schedule
        $dayOfWeek = $start->format('w');
        $schedule = $this->schedules()->where('day_of_week', $dayOfWeek)->first();
        
        if (!$schedule || !$schedule->is_available) {
            return false;
        }

        // Check for conflicting appointments
        $conflicting = $this->appointments()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_datetime', [$start, $end])
                    ->orWhereBetween('end_datetime', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_datetime', '<=', $start)
                            ->where('end_datetime', '>=', $end);
                    });
            })
            ->exists();

        return !$conflicting;
    }
}
