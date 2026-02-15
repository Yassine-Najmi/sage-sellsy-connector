<?php

namespace App\Filament\Resources\ProductSyncs\Pages;

use App\Filament\Resources\ProductSyncs\ProductSyncResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductSync extends ViewRecord
{
    protected static string $resource = ProductSyncResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
