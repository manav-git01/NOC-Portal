<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideHistory extends Model
{
    protected $table = 'guide_histories';

    protected $fillable = [
        'student_id',
        'old_guide_id',
        'new_guide_id',
        'changed_by',
    ];

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the old guide.
     */
    public function oldGuide(): BelongsTo
    {
        return $this->belongsTo(User::class, 'old_guide_id');
    }

    /**
     * Get the new guide.
     */
    public function newGuide(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_guide_id');
    }

    /**
     * Get the admin who changed the guide.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
