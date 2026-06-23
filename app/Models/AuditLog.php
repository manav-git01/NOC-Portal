<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_name',
        'action',
        'target',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
