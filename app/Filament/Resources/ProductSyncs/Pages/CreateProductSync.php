<?php

namespace App\Filament\Resources\ProductSyncs\Pages;

use App\Filament\Resources\ProductSyncs\ProductSyncResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductSync extends CreateRecord
{
    protected static string $resource = ProductSyncResource::class;
}
