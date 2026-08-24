<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $table = 'exchange_rates';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'gym_id',
        'rate_source',
        'rate',
        'previous_rate',
        'variation_percent',
        'effective_date',
        'effective_at',
        'change_type',
        'notes',
        'updated_by',
        'ip_address',
        'api_provider',
        'raw_payload',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'float',
        'previous_rate' => 'float',
        'variation_percent' => 'float',
        'effective_date' => 'date',
        'effective_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Gym to which this custom rate belongs (null = global).
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    /**
     * Relationship: User/Admin who created or modified the rate.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: Only active rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope: Active rate for a specific gym or global default.
     */
    public function scopeForGym($query, $gymId = null)
    {
        if (!empty($gymId) && $gymId !== 'all') {
            return $query->where('gym_id', $gymId);
        }
        return $query->whereNull('gym_id');
    }

    /**
     * Helper: Source human-readable label.
     */
    public function getSourceLabelAttribute()
    {
        return match ($this->rate_source) {
            'bcv' => 'Banco Central de Venezuela (BCV Oficial)',
            'enparalelovzla' => 'EnParaleloVzla (Paralelo)',
            'custom' => 'Tasa Personalizada (Manual)',
            default => ucfirst($this->rate_source),
        };
    }

    /**
     * Helper: Change type human-readable label.
     */
    public function getChangeTypeLabelAttribute()
    {
        return match ($this->change_type) {
            'auto_job' => 'Sincronización Automática (API)',
            'manual_override' => 'Modificación Manual',
            'emergency_update' => 'Ajuste de Emergencia',
            default => ucfirst($this->change_type),
        };
    }
}
