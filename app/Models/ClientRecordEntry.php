<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientRecordEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_record_id',
        'appointment_id',
        'created_by',
        'entry_type',
        'title',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Entry type labels for display
     */
    public const TYPE_LABELS = [
        'appointment_created' => 'Appointment Created',
        'appointment_confirmed' => 'Appointment Confirmed',
        'appointment_completed' => 'Appointment Completed',
        'appointment_cancelled' => 'Appointment Cancelled',
        'case_note' => 'Case Note',
        'document_uploaded' => 'Document Uploaded',
        'document_verified' => 'Document Verified',
        'lawyer_comment' => 'Lawyer Comment',
        'status_change' => 'Status Changed',
    ];

    /**
     * Entry type icons (Bootstrap Icons)
     */
    public const TYPE_ICONS = [
        'appointment_created' => 'bi-calendar-plus',
        'appointment_confirmed' => 'bi-calendar-check',
        'appointment_completed' => 'bi-check-circle',
        'appointment_cancelled' => 'bi-calendar-x',
        'case_note' => 'bi-journal-text',
        'document_uploaded' => 'bi-file-earmark-arrow-up',
        'document_verified' => 'bi-file-earmark-check',
        'lawyer_comment' => 'bi-chat-left-text',
        'status_change' => 'bi-arrow-repeat',
    ];

    /**
     * Entry type colors (Bootstrap)
     */
    public const TYPE_COLORS = [
        'appointment_created' => 'primary',
        'appointment_confirmed' => 'info',
        'appointment_completed' => 'success',
        'appointment_cancelled' => 'danger',
        'appointment_update' => 'info',
        'case_note' => 'secondary',
        'document_uploaded' => 'warning',
        'document_verified' => 'success',
        'lawyer_comment' => 'primary',
        'status_change' => 'dark',
    ];

    /**
     * Get the client record.
     */
    public function clientRecord(): BelongsTo
    {
        return $this->belongsTo(ClientRecord::class);
    }

    /**
     * Get the appointment.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the user who created this entry.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the label for the entry type
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->entry_type] ?? $this->entry_type;
    }

    /**
     * Get the icon for the entry type
     */
    public function getTypeIconAttribute(): string
    {
        return self::TYPE_ICONS[$this->entry_type] ?? 'bi-circle';
    }

    /**
     * Get the color for the entry type
     */
    public function getTypeColorAttribute(): string
    {
        return self::TYPE_COLORS[$this->entry_type] ?? 'secondary';
    }
}
