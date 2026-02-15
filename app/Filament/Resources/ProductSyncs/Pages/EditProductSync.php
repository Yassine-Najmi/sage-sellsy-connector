<?php

namespace App\Filament\Resources\ProductSyncs\Pages;

use App\Filament\Resources\ProductSyncs\ProductSyncResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProductSync extends EditRecord
{
    protected static string $resource = ProductSyncResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
