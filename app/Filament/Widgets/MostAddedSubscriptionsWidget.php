<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\CatalogueItem;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Story 7.8: Most-Added Subscriptions Ranking
 */
class MostAddedSubscriptionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top 10 Abonnements du Catalogue';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CatalogueItem::query()
                    ->orderBy('name', 'asc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex(),

                ImageColumn::make('logo_url')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=3B82F6'),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Streaming' => 'success',
                        'Musique' => 'info',
                        'Cloud' => 'warning',
                        'Jeux' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('typical_amount')
                    ->label('Prix Typique')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
