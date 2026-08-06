<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GymPaymentGateway extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'gym_payment_gateways';

    protected $fillable = [
        'gym_id',
        'provider',
        'title',
        'description',
        'is_active',
        'environment',
        'credentials',
        'extra_config',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credentials' => 'array',
        'extra_config' => 'array',
        'sort_order' => 'integer',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    /**
     * Helper to get public sanitized credentials for mobile APP API consumption
     */
    public function getPublicSanitizedCredentials()
    {
        $creds = $this->credentials ?? [];
        if (!is_array($creds)) {
            return [];
        }

        // Remove private keys/secrets before serving to mobile clients
        $sensitiveKeys = ['secret_key', 'api_secret', 'secret', 'access_token', 'private_key'];
        foreach ($sensitiveKeys as $key) {
            unset($creds[$key]);
        }

        return $creds;
    }
}
