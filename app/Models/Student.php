<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'guid',
        'user_guid',
        'sin',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'email',
        'address',
        'city',
        'zip_code',
        'bc_resident',
        'info_consent',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_guid', 'guid');
    }

    /**
     * Get the student demographics for this student.
     */
    public function demographics()
    {
        return $this->hasMany(StudentDemographic::class, 'student_guid', 'guid');
    }

    /**
     * Get the student demographic answers through demographics.
     */
    public function demographicAnswers()
    {
        return $this->hasManyThrough(
            StudentDemographicAnswer::class,
            StudentDemographic::class,
            'student_guid',
            'student_demographic_id',
            'guid',
            'id'
        );
    }

    /**
     * Get formatted demographic data for forms
     */
    public function getFormattedDemographics()
    {
        $demographics = [];
        
        $studentDemographics = $this->demographics()->with(['answers', 'demographic'])->get();
        
        foreach ($studentDemographics as $studentDemo) {
            $demographicId = $studentDemo->demographic_id;
            $answers = $studentDemo->answers;
            
            if ($answers->count() === 1) {
                // Single answer
                $demographics[$demographicId] = $answers->first()->value;
            } elseif ($answers->count() > 1) {
                // Multiple answers (checkboxes, multi-select)
                $demographics[$demographicId] = $answers->pluck('value')->implode(',');
            }
        }
        
        return $demographics;
    }

}
