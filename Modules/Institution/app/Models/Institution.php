<?php

namespace Modules\Institution\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Institution extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'institutions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'bceid_business_guid',
        'name',
        'display_name',
        'institution_type',
        'dli_number',
        'primary_contact_email',
        'primary_contact_phone',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'postal_code',
        'country',
        'website',
        'is_active',
        'status',
        'attestation_quota_total',
        'attestation_quota_grad',
        'attestation_quota_undergrad',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bceid_business_guid' => 'string',
        'is_active' => 'boolean',
        'attestation_quota_total' => 'integer',
        'attestation_quota_grad' => 'integer',
        'attestation_quota_undergrad' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all users associated with this institution.
     * Users are linked via bceid_business_guid.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'bceid_business_guid', 'bceid_business_guid');
    }

    /**
     * Get the institution's full address.
     *
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->province,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if institution has available attestation quota.
     *
     * @param string $type 'grad', 'undergrad', or 'total'
     * @return bool
     */
    public function hasAvailableQuota(string $type = 'total'): bool
    {
        $field = "attestation_quota_{$type}";
        return isset($this->$field) && $this->$field > 0;
    }

    /**
     * Scope a query to only include active institutions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
}