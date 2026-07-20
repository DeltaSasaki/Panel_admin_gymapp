<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    use HasFactory;

    protected $table = 'admin_audit_logs';

    public $timestamps = false;

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
        'old_data' => 'array',
        'new_data' => 'array',
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
     * Helper centralizado para registrar eventos de auditoría.
     */
    public static function record(string $actionType, string $tableName, $recordId = null, $oldData = null, $newData = null, $gymId = null)
    {
        try {
            $user = auth()->user();
            $adminId = $user ? $user->id : 1; // Fallback admin_id 1 si no hay sesión activa
            $effectiveGymId = $gymId ?? ($user ? $user->gym_id : null);

            return self::create([
                'gym_id' => $effectiveGymId,
                'admin_id' => $adminId,
                'action_type' => strtoupper($actionType),
                'table_name' => $tableName,
                'record_id' => $recordId ? (string) $recordId : null,
                'old_data' => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
                'new_data' => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'createdAt' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error registrando audit log: ' . $e->getMessage());
            return null;
        }
    }
}
