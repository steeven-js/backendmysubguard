<?php

namespace App\Filament\Resources\Suggestions;

use App\Filament\Resources\Suggestions\Pages\ListSuggestions;
use App\Models\CatalogueItem;
use App\Models\Suggestion;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use BackedEnum;

/**
 * Story 7.6: Community Suggestions Queue
 */
class SuggestionResource extends Resource
{
    protected static ?string $model = Suggestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static ?string $navigationLabel = 'Suggestions';

    protected static ?string $modelLabel = 'Suggestion';

    protected static ?string $pluralModelLabel = 'Suggestions';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) Suggestion::pending()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return Suggestion::pending()->count() > 0 ? 'warning' : 'gray';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('Nom suggéré')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('submission_count')
                    ->label('Nombre de soumissions')
                    ->numeric()
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom suggéré')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('submission_count')
                    ->label('Soumissions')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 10 => 'success',
                        $state >= 5 => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('reviewed_at')
                    ->label('Traité le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                    ])
                    ->default('pending'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Suggestion $record): bool => $record->status === 'pending')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom dans le catalogue')
                            ->default(fn (Suggestion $record): string => $record->name)
                            ->required(),

                        Forms\Components\TextInput::make('typical_amount')
                            ->label('Prix typique (EUR/mois)')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        Forms\Components\Select::make('category')
                            ->label('Catégorie')
                            ->options([
                                'streaming' => 'Streaming',
                                'music' => 'Musique',
                                'software' => 'Logiciel',
                                'gaming' => 'Jeux',
                                'news' => 'Presse',
                                'fitness' => 'Fitness',
                                'cloud' => 'Cloud',
                                'other' => 'Autre',
                            ])
                            ->required(),
                    ])
                    ->action(function (Suggestion $record, array $data): void {
                        // Create catalogue item
                        $catalogueItem = CatalogueItem::create([
                            'name' => $data['name'],
                            'typical_amount' => $data['typical_amount'],
                            'category' => $data['category'],
                        ]);

                        // Update suggestion
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'catalogue_item_id' => $catalogueItem->id,
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Suggestion approuvée')
                            ->body("'{$data['name']}' ajouté au catalogue.")
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Suggestion $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Rejeter cette suggestion ?')
                    ->modalDescription('Cette action marquera la suggestion comme rejetée.')
                    ->action(function (Suggestion $record): void {
                        $record->update([
                            'status' => 'rejected',
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Suggestion rejetée')
                            ->warning()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('reject_all')
                    ->label('Rejeter la sélection')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $records->each(function (Suggestion $record) {
                            if ($record->status === 'pending') {
                                $record->update([
                                    'status' => 'rejected',
                                    'reviewed_at' => now(),
                                ]);
                            }
                        });

                        Notification::make()
                            ->title('Suggestions rejetées')
                            ->warning()
                            ->send();
                    }),

                DeleteBulkAction::make()
                    ->label('Supprimer'),
            ])
            ->defaultSort('submission_count', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuggestions::route('/'),
        ];
    }
}
