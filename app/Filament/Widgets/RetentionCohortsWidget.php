<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Story 8.7: Retention Cohorts View (FR47)
 * Shows retention cohorts by signup week with D1, D7, D30 percentages
 */
class RetentionCohortsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Cohortes de Rétention';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // We'll use a fake query and override getTableRecords
                AnalyticsEvent::query()->whereRaw('1 = 0')
            )
            ->columns([
                Tables\Columns\TextColumn::make('week')
                    ->label('Semaine')
                    ->sortable(),

                Tables\Columns\TextColumn::make('users')
                    ->label('Nouveaux')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('d1')
                    ->label('D1')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $this->getRetentionColor($state)),

                Tables\Columns\TextColumn::make('d7')
                    ->label('D7')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $this->getRetentionColor($state)),

                Tables\Columns\TextColumn::make('d30')
                    ->label('D30')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $this->getRetentionColor($state)),
            ])
            ->paginated(false);
    }

    public function getTableRecords(): Collection
    {
        return $this->getCohortData();
    }

    /**
     * Calculate retention cohorts for the last 12 weeks
     */
    private function getCohortData(): Collection
    {
        $cohorts = collect();

        // Get the last 12 weeks
        for ($i = 11; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            // Users who downloaded the app this week
            $newUsers = AnalyticsEvent::ofType(AnalyticsEvent::EVENT_APP_DOWNLOADED)
                ->whereBetween('event_timestamp', [$weekStart, $weekEnd])
                ->distinct('anonymous_user_id')
                ->pluck('anonymous_user_id');

            $totalNewUsers = $newUsers->count();

            if ($totalNewUsers === 0) {
                continue;
            }

            // D1 retention: users who came back on day 1+
            $d1Retention = $this->calculateRetention($newUsers, $weekStart, 1);

            // D7 retention: users who came back on day 7+
            $d7Retention = $this->calculateRetention($newUsers, $weekStart, 7);

            // D30 retention: users who came back on day 30+
            $d30Retention = $this->calculateRetention($newUsers, $weekStart, 30);

            $cohorts->push((object) [
                'week' => $weekStart->format('d M'),
                'users' => $totalNewUsers,
                'd1' => $d1Retention !== null ? "{$d1Retention}%" : '-',
                'd7' => $d7Retention !== null ? "{$d7Retention}%" : '-',
                'd30' => $d30Retention !== null ? "{$d30Retention}%" : '-',
            ]);
        }

        return $cohorts;
    }

    /**
     * Calculate retention for a cohort at day N
     */
    private function calculateRetention(Collection $userIds, Carbon $cohortStart, int $day): ?int
    {
        // Check if enough time has passed to measure this retention
        $measureDate = $cohortStart->copy()->addDays($day);
        if ($measureDate->isFuture()) {
            return null;
        }

        $totalUsers = $userIds->count();
        if ($totalUsers === 0) {
            return null;
        }

        // Check retention events
        $eventType = match ($day) {
            1 => AnalyticsEvent::EVENT_RETENTION_D1,
            7 => AnalyticsEvent::EVENT_RETENTION_D7,
            30 => AnalyticsEvent::EVENT_RETENTION_D30,
            default => null,
        };

        if (!$eventType) {
            return null;
        }

        // Count users who triggered the retention event
        $retainedUsers = AnalyticsEvent::ofType($eventType)
            ->whereIn('anonymous_user_id', $userIds)
            ->distinct('anonymous_user_id')
            ->count('anonymous_user_id');

        return (int) round(($retainedUsers / $totalUsers) * 100);
    }

    /**
     * Get color based on retention percentage
     */
    private function getRetentionColor(string $value): string
    {
        if ($value === '-') {
            return 'gray';
        }

        $percentage = (int) str_replace('%', '', $value);

        if ($percentage >= 50) {
            return 'success';
        } elseif ($percentage >= 20) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
}
