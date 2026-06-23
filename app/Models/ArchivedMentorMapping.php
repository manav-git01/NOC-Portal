<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchivedMentorMapping extends Model
{
    protected $fillable = [
        'archive_id',
        'student_id',
        'student_name',
        'enrollment_number',
        'batch_id',
        'batch_name',
        'guide_id',
        'guide_name',
    ];

    /**
     * Get the parent archive.
     */
    public function archive(): BelongsTo
    {
        return $this->belongsTo(MentorMappingArchive::class, 'archive_id');
    }

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the batch.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    /**
     * Get the guide.
     */
    public function guide(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guide_id');
    }
}
