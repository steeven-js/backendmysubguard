<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;

/**
 * Story 7.5: Funnel Visualization
 * Downloads → First Subscription → 5+ Subscriptions → D7 Retention → D30 Retention
 */
class FunnelChartWidget extends ChartWidget
{
    protected ?string $heading = 'Entonnoir de Conversion';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        // Get counts for each funnel stage
        $downloads = AnalyticsEvent::ofType(AnalyticsEvent::EVENT_APP_DOWNLOADED)->count();
        $firstSubscription = AnalyticsEvent::ofType(AnalyticsEvent::EVENT_FIRST_SUBSCRIPTION)->count();
        $fiveSubscriptions = AnalyticsEvent::ofType(AnalyticsEvent::EVENT_FIVE_SUBSCRIPTIONS)->count();
        $retentionD7 = AnalyticsEvent::ofType(AnalyticsEvent::EVENT_RETENTION_D7)->count();
        $retentionD30 = AnalyticsEvent::ofType(AnalyticsEvent::EVENT_RETENTION_D30)->count();

        // Calculate conversion rates
        $convRate1 = $downloads > 0 ? round(($firstSubscription / $downloads) * 100, 1) : 0;
        $convRate2 = $firstSubscription > 0 ? round(($fiveSubscriptions / $firstSubscription) * 100, 1) : 0;
        $convRate3 = $downloads > 0 ? round(($retentionD7 / $downloads) * 100, 1) : 0;
        $convRate4 = $downloads > 0 ? round(($retentionD30 / $downloads) * 100, 1) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Utilisateurs',
                    'data' => [$downloads, $firstSubscription, $fiveSubscriptions, $retentionD7, $retentionD30],
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',   // Downloads - Blue
                        'rgba(16, 185, 129, 0.8)',  // First Sub - Green
                        'rgba(245, 158, 11, 0.8)', // 5+ Subs - Amber
                        'rgba(139, 92, 246, 0.8)', // D7 - Purple
                        'rgba(236, 72, 153, 0.8)', // D30 - Pink
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => [
                "Téléchargements ({$downloads})",
                "1er Abo ({$firstSubscription}) - {$convRate1}%",
                "5+ Abos ({$fiveSubscriptions}) - {$convRate2}%",
                "Rétention J7 ({$retentionD7}) - {$convRate3}%",
                "Rétention J30 ({$retentionD30}) - {$convRate4}%",
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
