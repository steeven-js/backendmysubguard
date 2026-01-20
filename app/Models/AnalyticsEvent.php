<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'anonymous_user_id',
        'event_type',
        'properties',
        'app_version',
        'os_version',
        'device_model',
        'event_timestamp',
    ];

    protected $casts = [
        'properties' => 'array',
        'event_timestamp' => 'datetime',
    ];

    // Event type constants
    public const EVENT_APP_DOWNLOADED = 'app_downloaded';
    public const EVENT_FIRST_SUBSCRIPTION = 'first_subscription_added';
    public const EVENT_FIVE_SUBSCRIPTIONS = 'five_subscriptions_reached';
    public const EVENT_RETENTION_D1 = 'retention_d1';
    public const EVENT_RETENTION_D7 = 'retention_d7';
    public const EVENT_RETENTION_D30 = 'retention_d30';
    public const EVENT_PREMIUM_CONVERSION = 'premium_conversion';

    // Scopes for common queries
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('anonymous_user_id', $userId);
    }

    public function scopeInDateRange($query, $start, $end)
    {
        return $query->whereBetween('event_timestamp', [$start, $end]);
    }

    // Count unique users
    public static function uniqueUsersCount(): int
    {
        return self::distinct('anonymous_user_id')->count('anonymous_user_id');
    }

    // Count active users in last N days
    public static function activeUsersCount(int $days = 30): int
    {
        return self::where('event_timestamp', '>=', now()->subDays($days))
            ->distinct('anonymous_user_id')
            ->count('anonymous_user_id');
    }
}
