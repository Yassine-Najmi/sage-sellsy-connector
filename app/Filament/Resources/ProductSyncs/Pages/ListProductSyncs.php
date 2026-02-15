<?php

namespace App\Filament\Resources\ProductSyncs\Pages;

use App\Filament\Resources\ProductSyncs\ProductSyncResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductSyncs extends ListRecords
{
    protected static string $resource = ProductSyncResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
