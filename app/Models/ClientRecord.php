<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientRecord extends Model
{
    use HasFactory;

    protected $fillable = [
    'first_name',
    'last_name',
    'email',
    'phone',
    'address',
    'barangay', // <-- ADD THIS
    'date_of_birth',
    'gender',
    'status',
    'notes',
];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the full name of the client
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get all appointments for this client record.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get all entries for this client record.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(ClientRecordEntry::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all documents for this client record.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Scope to search by name or email
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Find or create a client record by email
     */
    public static function findOrCreateByEmail(string $email, array $attributes = []): self
    {
        $client = static::where('email', $email)->first();

        if (!$client) {
            $client = static::create(array_merge(['email' => $email], $attributes));
        }

        return $client;
    }
}
