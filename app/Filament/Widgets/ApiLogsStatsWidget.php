<?php

namespace App\Filament\Widgets;

use App\Models\ApiLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class ApiLogsStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $today = Carbon::today();

        $totalToday = ApiLog::whereDate('created_at', $today)->count();
        $errorsToday = ApiLog::whereDate('created_at', $today)->where('status', 'error')->count();
        $openaiCalls = ApiLog::whereDate('created_at', $today)->where('service', 'openai')->count();
        $avgDuration = ApiLog::whereDate('created_at', $today)->whereNotNull('duration_ms')->avg('duration_ms');

        return [
            Stat::make('Requêtes aujourd\'hui', $totalToday)
                ->description('Total des appels API')
                ->color('primary'),

            Stat::make('Erreurs', $errorsToday)
                ->description('Échecs aujourd\'hui')
                ->color($errorsToday > 0 ? 'danger' : 'success'),

            Stat::make('Appels OpenAI', $openaiCalls)
                ->description('Scans de factures')
                ->color('info'),

            Stat::make('Temps moyen', $avgDuration ? round($avgDuration) . ' ms' : 'N/A')
                ->description('Latence moyenne')
                ->color($avgDuration && $avgDuration > 2000 ? 'warning' : 'success'),
        ];
    }
}
