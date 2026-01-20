<?php

namespace App\Filament\Resources\CatalogueItems\Pages;

use App\Filament\Resources\CatalogueItems\CatalogueItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCatalogueItem extends EditRecord
{
    protected static string $resource = CatalogueItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
