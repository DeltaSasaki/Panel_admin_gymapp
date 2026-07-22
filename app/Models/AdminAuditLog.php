<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    use HasFactory;

    protected $table = 'admin_audit_logs';

    public $timestamps = false; // Using custom createdAt column

    protected $fillable = [
        'gym_id',
        'admin_id',
        'action_type',
        'table_name',
        'record_id',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
        'createdAt',
    ];

    protected $casts = [
        'createdAt' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    /**
     * Static helper to cleanly record audit log entries.
     */
    public static function logAction($actionType, $tableName, $recordId = null, $oldData = null, $newData = null, $gymId = null, $adminId = null)
    {
        try {
            $currentAdminId = $adminId ?: (auth()->check() ? auth()->id() : null);
            if (!$currentAdminId) return;

            $currentGymId = $gymId !== null ? $gymId : (auth()->check() ? auth()->user()->gym_id : null);

            self::create([
                'gym_id' => $currentGymId,
                'admin_id' => $currentAdminId,
                'action_type' => $actionType,
                'table_name' => $tableName,
                'record_id' => $recordId ? (string) $recordId : null,
                'old_data' => is_array($oldData) || is_object($oldData) ? json_encode($oldData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $oldData,
                'new_data' => is_array($newData) || is_object($newData) ? json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $newData,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'createdAt' => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed recording audit log: ' . $e->getMessage());
        }
    }

    /**
     * Alias method for record / logAction.
     */
    public static function record($actionType, $tableName, $recordId = null, $oldData = null, $newData = null, $gymId = null, $adminId = null)
    {
        self::logAction($actionType, $tableName, $recordId, $oldData, $newData, $gymId, $adminId);
    }
}
