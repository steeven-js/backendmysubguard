<?php

namespace App\Filament\Resources\CatalogueItems\Pages;

use App\Filament\Resources\CatalogueItems\CatalogueItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCatalogueItem extends CreateRecord
{
    protected static string $resource = CatalogueItemResource::class;
}
