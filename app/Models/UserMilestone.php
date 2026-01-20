<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMilestone extends Model
{
    protected $fillable = [
        'milestone_type',
        'threshold_value',
        'actual_value',
        'notification_sent',
        'reached_at',
    ];

    protected $casts = [
        'notification_sent' => 'boolean',
        'reached_at' => 'datetime',
    ];

    // Milestone thresholds
    public const THRESHOLDS = [
        'users_1k' => 1000,
        'users_5k' => 5000,
        'users_10k' => 10000,
        'users_50k' => 50000,
        'users_100k' => 100000,
    ];

    // Check and record milestones
    public static function checkAndRecordMilestones(int $currentUserCount): array
    {
        $newMilestones = [];

        foreach (self::THRESHOLDS as $type => $threshold) {
            if ($currentUserCount >= $threshold) {
                $milestone = self::firstOrCreate(
                    ['milestone_type' => $type],
                    [
                        'threshold_value' => $threshold,
                        'actual_value' => $currentUserCount,
                        'reached_at' => now(),
                    ]
                );

                if ($milestone->wasRecentlyCreated) {
                    $newMilestones[] = $milestone;
                }
            }
        }

        return $newMilestones;
    }

    // Get unreported milestones
    public static function unreported()
    {
        return self::where('notification_sent', false)->get();
    }

    // Mark as notified
    public function markAsNotified(): void
    {
        $this->update(['notification_sent' => true]);
    }
}
