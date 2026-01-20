<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\CatalogueItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Story 7.4: Admin Dashboard - Key Metrics
 */
class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Total unique users (from analytics events)
        $totalUsers = AnalyticsEvent::uniqueUsersCount();

        // Active users (last 30 days)
        $activeUsers = AnalyticsEvent::activeUsersCount(30);

        // Previous week active users for trend
        $lastWeekActiveUsers = AnalyticsEvent::where('event_timestamp', '>=', now()->subDays(37))
            ->where('event_timestamp', '<', now()->subDays(7))
            ->distinct('anonymous_user_id')
            ->count('anonymous_user_id');

        $activeUsersTrend = $lastWeekActiveUsers > 0
            ? round((($activeUsers - $lastWeekActiveUsers) / $lastWeekActiveUsers) * 100, 1)
            : 0;

        // Total subscriptions tracked (sum of first_subscription_added events)
        $totalSubscriptions = AnalyticsEvent::ofType(AnalyticsEvent::EVENT_FIRST_SUBSCRIPTION)->count()
            + AnalyticsEvent::ofType(AnalyticsEvent::EVENT_FIVE_SUBSCRIPTIONS)->count() * 4; // Approximate

        // Premium conversions
        $premiumConversions = AnalyticsEvent::ofType(AnalyticsEvent::EVENT_PREMIUM_CONVERSION)->count();
        $lastWeekPremium = AnalyticsEvent::ofType(AnalyticsEvent::EVENT_PREMIUM_CONVERSION)
            ->where('event_timestamp', '>=', now()->subDays(7))
            ->count();

        // Catalogue items
        $catalogueItems = CatalogueItem::count();

        return [
            Stat::make('Total Utilisateurs', number_format($totalUsers))
                ->description('Utilisateurs uniques')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Utilisateurs Actifs (30j)', number_format($activeUsers))
                ->description($this->formatTrend($activeUsersTrend))
                ->descriptionIcon($activeUsersTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($activeUsersTrend >= 0 ? 'success' : 'danger'),

            Stat::make('Abonnements Suivis', number_format($totalSubscriptions))
                ->description('Total estimé')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('info'),

            Stat::make('Conversions Premium', number_format($premiumConversions))
                ->description("+{$lastWeekPremium} cette semaine")
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('Catalogue', number_format($catalogueItems))
                ->description('Abonnements référencés')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('gray'),
        ];
    }

    private function formatTrend(float $percentage): string
    {
        $sign = $percentage >= 0 ? '+' : '';
        return "{$sign}{$percentage}% vs semaine précédente";
    }
}
