<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Noc extends Model
{
    protected $fillable = [
        'application_id',
        'noc_number',
        'pdf_path',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    /**
     * Get the application that owns the NOC.
     */
    public function application()
    {
        return $this->belongsTo(InternshipApplication::class, 'application_id');
    }

    /**
     * Get the user who generated the NOC.
     */
    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Alias for generator relationship (used in PDF view).
     */
    public function generated_by_user()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
