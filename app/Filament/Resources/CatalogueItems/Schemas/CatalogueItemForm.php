<?php

namespace App\Filament\Resources\CatalogueItems\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CatalogueItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nom'),
                TextInput::make('typical_amount')
                    ->numeric()
                    ->prefix('€')
                    ->label('Montant typique'),
                FileUpload::make('logo_url')
                    ->image()
                    ->directory('logos')
                    ->disk('public')
                    ->imageEditor()
                    ->maxSize(512)
                    ->label('Logo'),
                Select::make('category')
                    ->required()
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
                    ->default('Autre')
                    ->label('Catégorie'),
            ]);
    }
}
