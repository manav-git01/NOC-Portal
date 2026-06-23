<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'name',
        'guide_id',
    ];

    /**
     * Get the designated guide for this batch.
     */
    public function guide()
    {
        return $this->belongsTo(User::class, 'guide_id');
    }

    /**
     * Get the students belonging to this batch.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'batch_id')->whereHas('role', function ($query) {
            $query->where('name', 'student');
        });
    }
}
