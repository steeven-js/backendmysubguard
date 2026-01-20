<?php

namespace App\Filament\Resources\CatalogueItems\Tables;

use App\Models\CatalogueItem;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class CatalogueItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_url')
                    ->label('Logo')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=3B82F6'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nom'),
                TextColumn::make('typical_amount')
                    ->money('EUR')
                    ->sortable()
                    ->label('Montant'),
                TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->label('Catégorie'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'Streaming' => 'Streaming',
                        'Musique' => 'Musique',
                        'Cloud' => 'Cloud',
                        'Productivité' => 'Productivité',
                        'Jeux' => 'Jeux',
                        'News' => 'News',
                        'Fitness' => 'Fitness',
                        'Autre' => 'Autre',
                    ])
                    ->label('Catégorie'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Story 7.7: Merge Duplicate Subscriptions
                    BulkAction::make('merge')
                        ->label('Fusionner')
                        ->icon('heroicon-o-arrows-pointing-in')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Fusionner les éléments sélectionnés')
                        ->modalDescription('Choisissez l\'élément principal à conserver. Les autres seront supprimés.')
                        ->form(function (Collection $records) {
                            return [
                                Forms\Components\Select::make('primary_id')
                                    ->label('Élément principal à conserver')
                                    ->options(
                                        $records->pluck('name', 'id')->toArray()
                                    )
                                    ->required()
                                    ->helperText('Les autres éléments seront fusionnés dans celui-ci puis supprimés.'),
                            ];
                        })
                        ->action(function (Collection $records, array $data): void {
                            $primaryId = $data['primary_id'];
                            $primary = CatalogueItem::find($primaryId);

                            if (!$primary) {
                                Notification::make()
                                    ->title('Erreur')
                                    ->body('Élément principal introuvable.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $mergedNames = [];
                            foreach ($records as $record) {
                                if ($record->id !== $primaryId) {
                                    $mergedNames[] = $record->name;

                                    // Log the merge for audit
                                    Log::info('Catalogue merge', [
                                        'merged_id' => $record->id,
                                        'merged_name' => $record->name,
                                        'into_id' => $primary->id,
                                        'into_name' => $primary->name,
                                        'user_id' => auth()->id(),
                                    ]);

                                    // Delete the duplicate
                                    $record->delete();
                                }
                            }

                            Notification::make()
                                ->title('Fusion réussie')
                                ->body(count($mergedNames) . ' élément(s) fusionné(s) dans "' . $primary->name . '".')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
