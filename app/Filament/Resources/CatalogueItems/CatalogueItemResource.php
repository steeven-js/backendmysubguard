<?php

namespace App\Filament\Resources\CatalogueItems;

use App\Filament\Resources\CatalogueItems\Pages\CreateCatalogueItem;
use App\Filament\Resources\CatalogueItems\Pages\EditCatalogueItem;
use App\Filament\Resources\CatalogueItems\Pages\ListCatalogueItems;
use App\Filament\Resources\CatalogueItems\Schemas\CatalogueItemForm;
use App\Filament\Resources\CatalogueItems\Tables\CatalogueItemsTable;
use App\Models\CatalogueItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CatalogueItemResource extends Resource
{
    protected static ?string $model = CatalogueItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Catalogue';

    protected static ?string $modelLabel = 'Abonnement';

    protected static ?string $pluralModelLabel = 'Catalogue';

    public static function form(Schema $schema): Schema
    {
        return CatalogueItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CatalogueItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCatalogueItems::route('/'),
            'create' => CreateCatalogueItem::route('/create'),
            'edit' => EditCatalogueItem::route('/{record}/edit'),
        ];
    }
}
