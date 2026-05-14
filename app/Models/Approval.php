<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'application_id',
        'approver_id',
        'approver_role',
        'status',
        'remarks',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Get the application that owns the approval.
     */
    public function application()
    {
        return $this->belongsTo(InternshipApplication::class, 'application_id');
    }

    /**
     * Get the user who approved.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
