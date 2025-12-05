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
        'guid',
        'bceid_business_guid',
        'dli',
        'name',
        'name_code',
        'size',
        'category',
        'economic_region',
        'legal_name',
        'address1',
        'address2',
        'primary_contact',
        'primary_email',
        'city',
        'postal_code',
        'province',
        'active_status',
        'last_touch_by_user_guid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bceid_business_guid' => 'string',
        'active_status' => 'boolean',
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
            $this->address1,
            $this->address2,
            $this->city,
            $this->province,
            $this->postal_code,
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
        return $query->where('active_status', true);
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