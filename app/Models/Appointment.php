<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'client_record_id',
        'lawyer_id',
        'ai_recommendation_id',
        'start_datetime',
        'end_datetime',
        'estimated_duration_minutes',
        'narrative',
        'professional_summary',
        'detected_services',
        'complexity_level',
        'document_checklist',
        'documents_submitted',
        'status',
        'confirmed_by',
        'confirmed_at',
        'cancellation_reason',
        'cancelled_at',
        'queue_number',
        'checked_in_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'detected_services' => 'array',
        'document_checklist' => 'array',
        'documents_submitted' => 'array',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_duration_minutes' => 'integer',
        'queue_number' => 'integer',
    ];

    /**
     * Status labels for display
     */
    public const STATUS_LABELS = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show',
        'rescheduled' => 'Rescheduled',
    ];

    /**
     * Status badge colors (Bootstrap)
     */
    public const STATUS_COLORS = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'in_progress' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        'no_show' => 'secondary',
        'rescheduled' => 'dark',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference_number)) {
                $model->reference_number = self::generateReferenceNumber();
            }
        });
    }

    /**
     * Generate a unique reference number
     */
    public static function generateReferenceNumber(): string
    {
        $prefix = 'APT';
        $date = date('Ymd');
        $random = strtoupper(Str::random(4));
        
        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Get the client record.
     */
    public function clientRecord(): BelongsTo
    {
        return $this->belongsTo(ClientRecord::class);
    }

    /**
     * Get the lawyer.
     */
    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }

    /**
     * Get the AI recommendation.
     */
    public function aiRecommendation(): BelongsTo
    {
        return $this->belongsTo(AiRecommendation::class);
    }

    /**
     * Get the user who confirmed this appointment.
     */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Get status color for Bootstrap badge
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->start_datetime->format('F j, Y');
    }

    /**
     * Get formatted time range
     */
    public function getFormattedTimeRangeAttribute(): string
    {
        return $this->start_datetime->format('g:i A') . ' - ' . $this->end_datetime->format('g:i A');
    }

    /**
     * Scope by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for today's appointments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('start_datetime', today());
    }

    /**
     * Scope for upcoming appointments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>=', now())
            ->whereIn('status', ['pending', 'confirmed']);
    }

    /**
     * Confirm the appointment
     */
    public function confirm(int $userId): self
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_by' => $userId,
            'confirmed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Cancel the appointment
     */
    public function cancel(?string $reason = null): self
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        return $this;
    }

    /**
     * Start the appointment
     */
    public function start(): self
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return $this;
    }

    /**
     * Complete the appointment
     */
    public function complete(): self
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Check in the client
     */
    public function checkIn(int $queueNumber): self
    {
        $this->update([
            'checked_in_at' => now(),
            'queue_number' => $queueNumber,
        ]);

        return $this;
    }
}
