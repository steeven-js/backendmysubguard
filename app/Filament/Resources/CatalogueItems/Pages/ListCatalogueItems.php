<?php

namespace App\Filament\Resources\CatalogueItems\Pages;

use App\Filament\Resources\CatalogueItems\CatalogueItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCatalogueItems extends ListRecords
{
    protected static string $resource = CatalogueItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
