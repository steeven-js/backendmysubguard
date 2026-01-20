<?php

namespace App\Jobs;

use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Models\UserMilestone;
use App\Notifications\UserMilestoneReached;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Story 7.9: User Threshold Alerts
 * Checks for new user milestones and sends notifications
 */
class CheckUserMilestones implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Get current unique user count from analytics
        $currentUserCount = AnalyticsEvent::uniqueUsersCount();

        Log::info('Checking user milestones', ['current_count' => $currentUserCount]);

        // Check and record any new milestones
        $newMilestones = UserMilestone::checkAndRecordMilestones($currentUserCount);

        if (empty($newMilestones)) {
            Log::info('No new milestones reached');
            return;
        }

        // Get admin users to notify
        $admins = User::where('is_admin', true)->get();

        // If no admin users, use all users (for development)
        if ($admins->isEmpty()) {
            $admins = User::all();
        }

        // Send notifications for each new milestone
        foreach ($newMilestones as $milestone) {
            Log::info('New milestone reached!', [
                'type' => $milestone->milestone_type,
                'threshold' => $milestone->threshold_value,
                'actual' => $milestone->actual_value,
            ]);

            // Send notifications
            Notification::send($admins, new UserMilestoneReached($milestone));

            // Mark as notified
            $milestone->markAsNotified();
        }
    }
}
