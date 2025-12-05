<?php

namespace Modules\Institution\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Attestation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attestations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'guid',
        'user_id',
        'user_guid',
        'institution_bceid_guid',
        'institution_id',
        'application_id',
        'attestation_number',
        'status',
        'attestation_type',
        'issue_date',
        'expiry_date',
        'declined_at',
        'revoked_at',
        'decline_reason',
        'revocation_reason',
        'declined_by_user_id',
        'revoked_by_user_id',
        'declined_by_user_guid',
        'revoked_by_user_guid',
        'program_name',
        'program_code',
        'program_start_date',
        'program_end_date',
        'notes',
        'document_path',
        'metadata',
        'issued_by_user_id',
        'issued_by_user_guid',
        'created_by_user_guid',
        'last_touch_by_user_guid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'guid' => 'string',
        'user_guid' => 'string',
        'institution_bceid_guid' => 'string',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'declined_at' => 'datetime',
        'revoked_at' => 'datetime',
        'program_start_date' => 'date',
        'program_end_date' => 'date',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attestation) {
            if (empty($attestation->guid)) {
                $attestation->guid = (string) Str::uuid();
            }
            if (empty($attestation->attestation_number)) {
                $attestation->attestation_number = self::generateAttestationNumber();
            }
            if (empty($attestation->issue_date)) {
                $attestation->issue_date = now()->toDateString();
            }
            // Set expiry date to 1 year from issue date if not provided
            if (empty($attestation->expiry_date) && !empty($attestation->issue_date)) {
                $attestation->expiry_date = Carbon::parse($attestation->issue_date)->addYear()->toDateString();
            }
        });
    }

    /**
     * Generate a unique attestation number.
     * Format: PAL-YYYY-XXXXXX
     *
     * @return string
     */
    protected static function generateAttestationNumber(): string
    {
        $year = date('Y');
        $lastAttestation = self::where('attestation_number', 'LIKE', "PAL-{$year}-%")
            ->orderBy('attestation_number', 'desc')
            ->first();

        if ($lastAttestation) {
            $lastNumber = (int) substr($lastAttestation->attestation_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('PAL-%s-%06d', $year, $newNumber);
    }

    /**
     * Get the student/user this attestation belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the institution this attestation belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    /**
     * Get the application this attestation is linked to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    /**
     * Get the user who issued this attestation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    /**
     * Get the user who declined this attestation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function declinedBy()
    {
        return $this->belongsTo(User::class, 'declined_by_user_id');
    }

    /**
     * Get the user who revoked this attestation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    /**
     * Scope a query to only include attestations for a specific institution.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $bceidGuid
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForInstitution($query, string $bceidGuid)
    {
        return $query->where('institution_bceid_guid', $bceidGuid);
    }

    /**
     * Scope a query to filter by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by attestation type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType($query, string $type)
    {
        return $query->where('attestation_type', $type);
    }

    /**
     * Scope a query to only include issued attestations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    /**
     * Scope a query to only include declined attestations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    /**
     * Scope a query to only include expired attestations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function($q) {
                $q->where('status', 'issued')
                  ->whereNotNull('expiry_date')
                  ->where('expiry_date', '<', now());
            });
    }

    /**
     * Check if attestation is issued.
     *
     * @return bool
     */
    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    /**
     * Check if attestation is declined.
     *
     * @return bool
     */
    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    /**
     * Check if attestation is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->expiry_date && Carbon::parse($this->expiry_date)->isPast()) {
            return true;
        }

        return false;
    }

    /**
     * Check if attestation is revoked.
     *
     * @return bool
     */
    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    /**
     * Check if attestation is active (issued and not expired).
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isIssued() && !$this->isExpired();
    }

    /**
     * Get status badge color.
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->isExpired()) {
            return 'secondary';
        }

        return match($this->status) {
            'issued' => 'success',
            'declined' => 'danger',
            'expired' => 'secondary',
            'revoked' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get student's full name.
     *
     * @return string
     */
    public function getStudentNameAttribute(): string
    {
        if ($this->user) {
            return trim("{$this->user->first_name} {$this->user->last_name}");
        }
        return 'Unknown Student';
    }

    /**
     * Get issuer's full name.
     *
     * @return string|null
     */
    public function getIssuerNameAttribute(): ?string
    {
        if ($this->issuedBy) {
            return trim("{$this->issuedBy->first_name} {$this->issuedBy->last_name}");
        }
        return null;
    }

    /**
     * Get days until expiry.
     *
     * @return int|null
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return Carbon::now()->diffInDays(Carbon::parse($this->expiry_date), false);
    }

    /**
     * Check if attestation is expiring soon (within 30 days).
     *
     * @return bool
     */
    public function isExpiringSoon(): bool
    {
        $daysUntilExpiry = $this->getDaysUntilExpiryAttribute();
        return $daysUntilExpiry !== null && $daysUntilExpiry > 0 && $daysUntilExpiry <= 30;
    }
}
