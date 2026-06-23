<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MentorMappingArchive extends Model
{
    protected $fillable = [
        'import_date',
        'imported_by',
        'file_name',
        'file_path',
        'total_students',
        'total_guides',
        'total_batches',
        'import_notes',
    ];

    protected $casts = [
        'import_date' => 'datetime',
    ];

    /**
     * Get the user who imported the mapping.
     */
    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /**
     * Get the archived mappings.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ArchivedMentorMapping::class, 'archive_id');
    }

    /**
     * Snapshots the current database mappings to the archive.
     */
    public static function archiveCurrentState($importedByUserId, $fileName, $importNotes = null, $filePath = null)
    {
        $studentRole = Role::where('name', 'student')->first();
        if (!$studentRole) {
            return null;
        }

        // Get all students with their batch and guide
        $students = User::where('role_id', $studentRole->id)
            ->with(['batch', 'guide'])
            ->get();

        if ($students->isEmpty()) {
            return null;
        }

        $totalStudents = $students->count();
        $totalGuides = $students->whereNotNull('guide_id')->pluck('guide_id')->unique()->count();
        $totalBatches = $students->whereNotNull('batch_id')->pluck('batch_id')->unique()->count();

        $archive = self::create([
            'import_date' => now(),
            'imported_by' => $importedByUserId,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'total_students' => $totalStudents,
            'total_guides' => $totalGuides,
            'total_batches' => $totalBatches,
            'import_notes' => $importNotes,
        ]);

        foreach ($students as $student) {
            ArchivedMentorMapping::create([
                'archive_id' => $archive->id,
                'student_id' => $student->id,
                'student_name' => $student->name,
                'enrollment_number' => $student->enrollment_number ?? 'N/A',
                'batch_id' => $student->batch_id,
                'batch_name' => $student->batch?->name ?? 'N/A',
                'guide_id' => $student->guide_id,
                'guide_name' => $student->guide?->name ?? 'N/A',
            ]);
        }

        return $archive;
    }
}
