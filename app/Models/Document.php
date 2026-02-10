<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_record_id',
        'appointment_id',
        'uploaded_by',
        'name',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'description',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'file_size' => 'integer',
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
     * Get the user who uploaded this document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the user who verified this document.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    /**
     * Verify the document
     */
    public function verify(int $userId): self
    {
        $this->update([
            'status' => 'verified',
            'verified_by' => $userId,
            'verified_at' => now(),
        ]);

        return $this;
    }

    /**
     * Reject the document
     */
    public function reject(int $userId, string $reason): self
    {
        $this->update([
            'status' => 'rejected',
            'verified_by' => $userId,
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $this;
    }

    /**
     * Scope by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending documents
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
