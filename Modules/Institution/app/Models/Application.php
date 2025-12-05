<?php

namespace Modules\Institution\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Support\Str;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'applications';

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
        'application_number',
        'status',
        'program_level',
        'program_name',
        'program_code',
        'program_start_date',
        'program_end_date',
        'requested_amount',
        'approved_amount',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'withdrawn_at',
        'reviewed_by_user_id',
        'reviewed_by_user_guid',
        'review_notes',
        'rejection_reason',
        'student_notes',
        'attestation_required',
        'attestation_id',
        'supporting_documents',
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
        'program_start_date' => 'date',
        'program_end_date' => 'date',
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'attestation_required' => 'boolean',
        'supporting_documents' => 'array',
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

        static::creating(function ($application) {
            if (empty($application->guid)) {
                $application->guid = (string) Str::uuid();
            }
            if (empty($application->application_number)) {
                $application->application_number = self::generateApplicationNumber();
            }
        });
    }

    /**
     * Generate a unique application number.
     * Format: APP-YYYY-XXXXXX
     *
     * @return string
     */
    protected static function generateApplicationNumber(): string
    {
        $year = date('Y');
        $lastApplication = self::where('application_number', 'LIKE', "APP-{$year}-%")
            ->orderBy('application_number', 'desc')
            ->first();

        if ($lastApplication) {
            $lastNumber = (int) substr($lastApplication->application_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('APP-%s-%06d', $year, $newNumber);
    }

    /**
     * Get the student/user who submitted the application.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the institution this application belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    /**
     * Get the reviewer who reviewed this application.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /**
     * Scope a query to only include applications for a specific institution.
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
     * Scope a query to filter by program level.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $level
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProgramLevel($query, string $level)
    {
        return $query->where('program_level', $level);
    }

    /**
     * Scope a query to only include submitted applications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at')
            ->where('status', '!=', 'draft');
    }

    /**
     * Scope a query to only include pending applications (submitted but not yet reviewed).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review', 'additional_info_required']);
    }

    /**
     * Check if application is in draft status.
     *
     * @return bool
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if application is submitted.
     *
     * @return bool
     */
    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null && $this->status !== 'draft';
    }

    /**
     * Check if application is approved.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if application is rejected.
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if application can be reviewed.
     *
     * @return bool
     */
    public function canBeReviewed(): bool
    {
        return in_array($this->status, ['submitted', 'under_review', 'additional_info_required']);
    }

    /**
     * Get status badge color.
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'submitted' => 'primary',
            'under_review' => 'info',
            'additional_info_required' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'withdrawn' => 'dark',
            'expired' => 'secondary',
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
     * Get reviewer's full name.
     *
     * @return string|null
     */
    public function getReviewerNameAttribute(): ?string
    {
        if ($this->reviewer) {
            return trim("{$this->reviewer->first_name} {$this->reviewer->last_name}");
        }
        return null;
    }
}