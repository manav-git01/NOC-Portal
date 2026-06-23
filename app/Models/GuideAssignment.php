<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideAssignment extends Model
{
    protected $fillable = [
        'student_id',
        'guide_id',
        'assigned_by',
        'assigned_at',
        'unassigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
    ];

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the guide.
     */
    public function guide(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guide_id');
    }

    /**
     * Get the user who assigned the guide.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
