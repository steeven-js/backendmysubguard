<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $fillable = [
        'type',
        'service',
        'method',
        'endpoint',
        'status',
        'status_code',
        'request_headers',
        'request_body',
        'response_body',
        'duration_ms',
        'ip_address',
        'user_agent',
        'error_message',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'request_body' => 'array',
        'response_body' => 'array',
    ];

    // Types
    public const TYPE_INCOMING = 'incoming';
    public const TYPE_OUTGOING = 'outgoing';

    // Services
    public const SERVICE_APP = 'app';
    public const SERVICE_OPENAI = 'openai';
    public const SERVICE_CATALOGUE = 'catalogue';
    public const SERVICE_ANALYTICS = 'analytics';

    // Statuses
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';
    public const STATUS_PENDING = 'pending';

    /**
     * Scope for incoming requests
     */
    public function scopeIncoming($query)
    {
        return $query->where('type', self::TYPE_INCOMING);
    }

    /**
     * Scope for outgoing requests
     */
    public function scopeOutgoing($query)
    {
        return $query->where('type', self::TYPE_OUTGOING);
    }

    /**
     * Scope for a specific service
     */
    public function scopeService($query, string $service)
    {
        return $query->where('service', $service);
    }

    /**
     * Get status badge color for Filament
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'success',
            self::STATUS_ERROR => 'danger',
            self::STATUS_PENDING => 'warning',
            default => 'gray',
        };
    }

    /**
     * Get type badge color for Filament
     */
    public function getTypeColor(): string
    {
        return match ($this->type) {
            self::TYPE_INCOMING => 'info',
            self::TYPE_OUTGOING => 'warning',
            default => 'gray',
        };
    }
}
