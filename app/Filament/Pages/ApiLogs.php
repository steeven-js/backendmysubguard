<?php

namespace App\Filament\Pages;

use App\Models\ApiLog;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;

class ApiLogs extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'API Logs';

    protected static ?string $title = 'Logs API en temps réel';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.api-logs';

    public function table(Table $table): Table
    {
        return $table
            ->query(ApiLog::query()->latest())
            ->poll('5s') // Auto-refresh every 5 seconds
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'incoming' => 'info',
                        'outgoing' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'incoming' => '← Entrant',
                        'outgoing' => '→ Sortant',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('service')
                    ->label('Service')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'openai' => 'purple',
                        'app' => 'blue',
                        'catalogue' => 'green',
                        'analytics' => 'orange',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                Tables\Columns\TextColumn::make('method')
                    ->label('Méthode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'GET' => 'success',
                        'POST' => 'info',
                        'PUT', 'PATCH' => 'warning',
                        'DELETE' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('endpoint')
                    ->label('Endpoint')
                    ->limit(40)
                    ->tooltip(fn (ApiLog $record): string => $record->endpoint),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'error' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status_code')
                    ->label('Code')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state >= 200 && $state < 300 => 'success',
                        $state >= 400 && $state < 500 => 'warning',
                        $state >= 500 => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('duration_ms')
                    ->label('Durée')
                    ->suffix(' ms')
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state < 500 => 'success',
                        $state < 2000 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'incoming' => 'Entrant',
                        'outgoing' => 'Sortant',
                    ]),

                Tables\Filters\SelectFilter::make('service')
                    ->label('Service')
                    ->options([
                        'openai' => 'OpenAI',
                        'app' => 'App',
                        'catalogue' => 'Catalogue',
                        'analytics' => 'Analytics',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'success' => 'Succès',
                        'error' => 'Erreur',
                        'pending' => 'En cours',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->modalHeading(fn (ApiLog $record): string => "Log #{$record->id}")
                    ->modalContent(fn (ApiLog $record) => view('filament.pages.api-log-detail', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->striped();
    }

    // Temporarily disabled - investigate icon rendering issue
    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         \App\Filament\Widgets\ApiLogsStatsWidget::class,
    //     ];
    // }
}
