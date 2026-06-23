<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'enrollment_number',
        'department',
        'semester',
        'batch_id',
        'guide_id',
        'faculty_id',
        'designation',
        'status',
        'account_status',
        'is_locked',
        'authority_type',
        'must_change_password',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the internship applications for the user.
     */
    public function internshipApplications()
    {
        return $this->hasMany(InternshipApplication::class);
    }

    /**
     * Get the approvals made by the user.
     */
    public function approvals()
    {
        return $this->hasMany(Approval::class, 'approver_id');
    }

    /**
     * Get the batch this student belongs to.
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    /**
     * Get the guide assigned to this student.
     */
    public function guide()
    {
        return $this->belongsTo(User::class, 'guide_id');
    }

    /**
     * Get the students assigned to this guide.
     */
    public function students()
    {
        return $this->hasMany(User::class, 'guide_id');
    }

    /**
     * Get the history of guide assignments for this student.
     */
    public function guideAssignments()
    {
        return $this->hasMany(GuideAssignment::class, 'student_id');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($roleName)
    {
        if (!$this->role) {
            return false;
        }
        
        $userRole = $this->role->name;
        
        if ($roleName === 'admin' || $roleName === 'student') {
            return $userRole === $roleName;
        }
        
        if ($roleName === 'faculty') {
            return in_array($userRole, ['faculty', 'higher_faculty']) || $this->permissions()->exists();
        }
        
        if ($roleName === 'higher_faculty') {
            return $userRole === 'higher_faculty' || $this->hasPermission('noc_authority');
        }
        
        return $userRole === $roleName;
    }

    /**
     * Check if user is a student.
     */
    public function isStudent()
    {
        return $this->hasRole('student');
    }

    /**
     * Check if user is a faculty.
     */
    public function isFaculty()
    {
        return $this->hasRole('faculty');
    }

    /**
     * Check if user is a higher faculty.
     */
    public function isHigherFaculty()
    {
        return $this->hasRole('higher_faculty');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Permissions relationship
     */
    public function permissions()
    {
        return $this->hasMany(FacultyPermission::class, 'user_id');
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission($permission)
    {
        return $this->permissions->contains('permission', $permission);
    }

    /**
     * Assign a permission
     */
    public function assignPermission($permission)
    {
        return $this->permissions()->firstOrCreate(['permission' => $permission]);
    }

    /**
     * Revoke a permission
     */
    public function revokePermission($permission)
    {
        return $this->permissions()->where('permission', $permission)->delete();
    }

    /**
     * Sync permissions
     */
    public function syncPermissions(array $permissions)
    {
        $this->permissions()->whereNotIn('permission', $permissions)->delete();
        foreach ($permissions as $perm) {
            $this->permissions()->firstOrCreate(['permission' => $perm]);
        }
    }

    /**
     * Check if user is an approval faculty (can approve/reject internships).
     */
    public function isApprovalFaculty()
    {
        return $this->hasPermission('approval_faculty');
    }

    /**
     * Check if user is a NOC authority (can generate NOCs, final approval).
     */
    public function isNocAuthority()
    {
        return $this->hasPermission('noc_authority');
    }

    /**
     * Check if user is a guide faculty.
     */
    public function isGuideFaculty()
    {
        return $this->hasPermission('guide') || $this->students()->exists();
    }

    /**
     * Get the display name for the user's authority type.
     */
    public function getAuthorityDisplayAttribute()
    {
        if ($this->isAdmin()) {
            return 'Administrator';
        }
        if ($this->isStudent()) {
            return 'Student';
        }
        
        $perms = $this->permissions->pluck('permission')->toArray();
        if (empty($perms)) {
            return $this->isFaculty() ? 'Guide Faculty' : 'N/A';
        }
        
        $labels = [];
        if (in_array('guide', $perms)) {
            $labels[] = 'Guide Faculty';
        }
        if (in_array('approval_faculty', $perms)) {
            $labels[] = 'Approval Faculty';
        }
        if (in_array('noc_authority', $perms)) {
            $labels[] = 'NOC Authority';
        }
        
        return implode(', ', $labels);
    }

    /**
     * Get the initials for the user's name.
     */
    public function getInitialsAvatarAttribute()
    {
        $words = preg_split("/\s+/", trim($this->name));
        $initials = '';
        foreach ($words as $w) {
            $initials .= mb_substr($w, 0, 1);
        }
        if (mb_strlen($initials) > 2) {
            $initials = mb_substr($initials, 0, 2);
        }
        return mb_strtoupper($initials);
    }

    /**
     * Check if the user's guide assignment is locked.
     */
    public function isLocked(): bool
    {
        return (bool) $this->is_locked;
    }

    /**
     * Check if the user account is active.
     */
    public function isActive(): bool
    {
        return $this->account_status === 'active';
    }

    /**
     * Scope a query to only include students without a guide.
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('guide_id');
    }

    /**
     * Scope a query to only include students with a guide.
     */
    public function scopeAssigned($query)
    {
        return $query->whereNotNull('guide_id');
    }
}
