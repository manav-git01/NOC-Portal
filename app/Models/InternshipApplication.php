<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipApplication extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'company_address',
        'company_email',
        'company_phone',
        'company_website',
        'branch_address',
        'number_of_employees',
        'branch_locations',
        'head_office_address',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_email',
        'hr_name',
        'hr_phone',
        'hr_email',
        'technology',
        'current_project',
        'clients',
        'how_did_you_get_company',
        'reason_to_select_company',
        'internship_position',
        'start_date',
        'end_date',
        'internship_description',
        'company_letter_path',
        'additional_documents',
        'status',
        'noc_requested',
        'faculty_remarks',
        'higher_faculty_remarks',
        'submitted_at',
        'faculty_reviewed_at',
        'higher_faculty_reviewed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'submitted_at' => 'datetime',
        'faculty_reviewed_at' => 'datetime',
        'higher_faculty_reviewed_at' => 'datetime',
        'additional_documents' => 'array',
        'noc_requested' => 'boolean',
    ];

    /**
     * Get the user that owns the application.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the approvals for the application.
     */
    public function approvals()
    {
        return $this->hasMany(Approval::class, 'application_id');
    }

    /**
     * Get the NOC for the application.
     */
    public function noc()
    {
        return $this->hasOne(Noc::class, 'application_id');
    }

    /**
     * Check if application is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if application is approved by faculty.
     */
    public function isFacultyApproved()
    {
        return $this->status === 'faculty_approved';
    }

    /**
     * Check if application is approved by higher faculty.
     */
    public function isHigherFacultyApproved()
    {
        return $this->status === 'higher_faculty_approved';
    }

    /**
     * Check if NOC is generated.
     */
    public function hasNoc()
    {
        return $this->status === 'noc_generated';
    }

    /**
     * Check if application is rejected.
     */
    public function isRejected()
    {
        return in_array($this->status, ['faculty_rejected', 'higher_faculty_rejected']);
    }

    /**
     * Check if NOC can be requested.
     */
    public function canRequestNoc()
    {
        return !$this->noc_requested && in_array($this->status, ['faculty_approved', 'faculty_rejected']);
    }

    /**
     * Check if NOC has been requested.
     */
    public function hasNocRequested()
    {
        return $this->noc_requested;
    }

    /**
     * Check if application is pending higher faculty review.
     */
    public function isPendingHigher()
    {
        return $this->status === 'pending_higher';
    }
}
