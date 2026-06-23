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
        'action_type',
        'target',
        'details',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'details' => 'array',
    ];

    /**
     * Helper to log an admin action.
     */
    public static function log(string $action, string $target, ?string $actionType = null, $details = null)
    {
        return self::create([
            'admin_name' => auth()->user() ? auth()->user()->name : 'System/Guest',
            'action' => $action,
            'action_type' => $actionType,
            'target' => $target,
            'details' => $details,
            'timestamp' => now(),
        ]);
    }
}
