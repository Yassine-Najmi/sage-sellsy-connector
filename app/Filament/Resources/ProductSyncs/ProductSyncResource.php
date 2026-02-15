<?php

namespace App\Filament\Resources\ProductSyncs;

use App\Filament\Resources\ProductSyncs\Pages\CreateProductSync;
use App\Filament\Resources\ProductSyncs\Pages\EditProductSync;
use App\Filament\Resources\ProductSyncs\Pages\ListProductSyncs;
use App\Filament\Resources\ProductSyncs\Pages\ViewProductSync;
use App\Filament\Resources\ProductSyncs\Schemas\ProductSyncForm;
use App\Filament\Resources\ProductSyncs\Schemas\ProductSyncInfolist;
use App\Filament\Resources\ProductSyncs\Tables\ProductSyncsTable;
use App\Models\ProductSync;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductSyncResource extends Resource
{
    protected static ?string $model = ProductSync::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowPath;

    // protected static ?string $navigationLabel = 'Sync Batches';

    protected static ?int $navigationSort = 2;




    public static function form(Schema $schema): Schema
    {
        return ProductSyncForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductSyncInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductSyncsTable::configure($table);
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
            'index' => ListProductSyncs::route('/'),
            'create' => CreateProductSync::route('/create'),
            'view' => ViewProductSync::route('/{record}'),
            'edit' => EditProductSync::route('/{record}/edit'),
        ];
    }
}
