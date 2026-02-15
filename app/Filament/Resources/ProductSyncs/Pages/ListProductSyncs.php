<?php

namespace App\Filament\Resources\ProductSyncs\Pages;

use App\Filament\Resources\ProductSyncs\ProductSyncResource;
use App\Models\ProductSync;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProductSyncs extends ListRecords
{
    protected static string $resource = ProductSyncResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => ProductSync::where('status', 'pending')->count()),

            'processing' => Tab::make('Processing')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'processing'))
                ->badge(fn () => ProductSync::where('status', 'processing')->count()),

            'success' => Tab::make('Success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'success'))
                ->badge(fn () => ProductSync::where('status', 'success')->count()),

            'failed' => Tab::make('Failed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'failed'))
                ->badge(fn () => ProductSync::where('status', 'failed')->count())
                ->badgeColor('danger'),

            'skipped' => Tab::make('Skipped')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'skipped'))
                ->badge(fn () => ProductSync::where('status', 'skipped')->count())
                ->badgeColor('warning'),
        ];
    }
}
