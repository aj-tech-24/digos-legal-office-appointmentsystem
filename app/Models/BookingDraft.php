<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BookingDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'client_email',
        'draft_state',
        'current_step',
        'privacy_accepted',
        'expires_at',
    ];

    protected $casts = [
        'draft_state' => 'array',
        'privacy_accepted' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->session_id)) {
                $model->session_id = Str::uuid();
            }
            if (empty($model->expires_at)) {
                $model->expires_at = now()->addHours(24);
            }
            if (empty($model->draft_state)) {
                $model->draft_state = [];
            }
        });
    }

    /**
     * Update draft state with new data
     */
    public function updateState(array $data): self
    {
        $currentState = $this->draft_state ?? [];
        $this->draft_state = array_merge($currentState, $data);
        $this->save();

        return $this;
    }

    /**
     * Get a value from the draft state
     */
    public function getStateValue(string $key, $default = null)
    {
        return $this->draft_state[$key] ?? $default;
    }

    /**
     * Move to the next step
     */
    public function nextStep(): self
    {
        $this->current_step = $this->current_step + 1;
        $this->save();

        return $this;
    }

    /**
     * Move to a specific step
     */
    public function goToStep(int $step): self
    {
        $this->current_step = $step;
        $this->save();

        return $this;
    }

    /**
     * Check if the draft is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }    /**
     * Extend expiration
     */
    public function extendExpiration(int $hours = 24): self
    {
        $this->expires_at = now()->addHours($hours);
        $this->save();

        return $this;
    }

    /**
     * Find or create a draft by session ID
     */
    public static function findOrCreateBySession(string $sessionId): self
    {
        $draft = static::where('session_id', $sessionId)
            ->where('expires_at', '>', now())
            ->first();

        if (!$draft) {
            $draft = static::create([
                'session_id' => $sessionId,
            ]);
        }

        return $draft;
    }

    /**
     * Scope to get non-expired drafts
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Clean up expired drafts
     */
    public static function cleanupExpired(): int
    {
        return static::where('expires_at', '<', now())->delete();
    }
}
