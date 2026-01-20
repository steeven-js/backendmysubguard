<?php

namespace App\Notifications;

use App\Models\UserMilestone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

/**
 * Story 7.9: User Threshold Alerts
 */
class UserMilestoneReached extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public UserMilestone $milestone
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        // Add Slack if configured
        if (config('services.slack.notifications.bot_user_oauth_token')) {
            $channels[] = 'slack';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $thresholdLabel = $this->getThresholdLabel();

        return (new MailMessage)
            ->subject("🎉 MySubGuard a atteint {$thresholdLabel} utilisateurs !")
            ->greeting('Félicitations !')
            ->line("MySubGuard vient d'atteindre un nouveau cap : **{$thresholdLabel} utilisateurs** !")
            ->line("Nombre exact : {$this->milestone->actual_value} utilisateurs")
            ->line("Atteint le : {$this->milestone->reached_at->format('d/m/Y à H:i')}")
            ->action('Voir le Dashboard', url('/admin'))
            ->line('Continuez comme ça !');
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        $thresholdLabel = $this->getThresholdLabel();

        return (new SlackMessage)
            ->success()
            ->content("🎉 *MySubGuard a atteint {$thresholdLabel} utilisateurs !*")
            ->attachment(function ($attachment) {
                $attachment
                    ->title('Détails du jalon')
                    ->fields([
                        'Seuil atteint' => $this->getThresholdLabel(),
                        'Nombre exact' => number_format($this->milestone->actual_value),
                        'Date' => $this->milestone->reached_at->format('d/m/Y H:i'),
                    ]);
            });
    }

    private function getThresholdLabel(): string
    {
        return match ($this->milestone->threshold_value) {
            1000 => '1 000',
            5000 => '5 000',
            10000 => '10 000',
            50000 => '50 000',
            100000 => '100 000',
            default => number_format($this->milestone->threshold_value),
        };
    }

    public function toArray(object $notifiable): array
    {
        return [
            'milestone_type' => $this->milestone->milestone_type,
            'threshold_value' => $this->milestone->threshold_value,
            'actual_value' => $this->milestone->actual_value,
            'reached_at' => $this->milestone->reached_at->toISOString(),
        ];
    }
}
