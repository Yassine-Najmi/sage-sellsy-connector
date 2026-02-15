<?php

namespace App\Filament\Resources\ProductSyncs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductSyncInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('syncBatch.id')
                    ->label('Sync batch'),
                TextEntry::make('ar_ref'),
                TextEntry::make('ar_design')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('ar_prixven')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('ar_prixach')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('ta_code')
                    ->placeholder('-'),
                TextEntry::make('ta_taux')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('sellsy_product_id')
                    ->placeholder('-'),
                TextEntry::make('sellsy_category_id')
                    ->placeholder('-'),
                TextEntry::make('sellsy_tax_id')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('retry_count')
                    ->numeric(),
                TextEntry::make('max_retries')
                    ->numeric(),
                TextEntry::make('last_attempt_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('error_message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('http_status_code')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
