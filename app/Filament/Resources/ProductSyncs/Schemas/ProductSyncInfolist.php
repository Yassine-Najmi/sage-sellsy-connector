<?php

namespace App\Filament\Resources\ProductSyncs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductSyncInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Information')
                    ->schema([
                        TextEntry::make('ar_ref')
                            ->label('Product Reference')
                            ->copyable(),
                        TextEntry::make('ar_design')
                            ->label('Description')
                            ->columnSpanFull(),
                        TextEntry::make('ar_prixven')
                            ->label('Sale Price')
                            ->money('EUR'),
                        TextEntry::make('ar_prixach')
                            ->label('Purchase Price')
                            ->money('EUR'),
                        TextEntry::make('ta_code')
                            ->label('Tax Code'),
                        TextEntry::make('ta_taux')
                            ->label('Tax Rate')
                            ->suffix('%'),
                    ])
                    ->columns(3),

                Section::make('Sync Status')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'gray',
                                'processing' => 'info',
                                'success' => 'success',
                                'failed' => 'danger',
                                'skipped' => 'warning',
                            }),
                        TextEntry::make('retry_count')
                            ->label('Retry Count'),
                        TextEntry::make('max_retries')
                            ->label('Max Retries'),
                        TextEntry::make('http_status_code')
                            ->label('HTTP Status Code')
                            ->badge(),
                        TextEntry::make('last_attempt_at')
                            ->label('Last Attempt')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Sellsy Information')
                    ->schema([
                        TextEntry::make('sellsy_product_id')
                            ->label('Sellsy Product ID')
                            ->copyable()
                            ->placeholder('Not synced yet'),
                        TextEntry::make('sellsy_category_id')
                            ->label('Sellsy Category ID')
                            ->placeholder('Not mapped'),
                        TextEntry::make('sellsy_tax_id')
                            ->label('Sellsy Tax ID')
                            ->placeholder('Not mapped'),
                    ])
                    ->columns(3)
                    ->visible(fn($record) => $record->status === 'success'),

                Section::make('Error Details')
                    ->schema([
                        TextEntry::make('error_message')
                            ->label('Error Message')
                            ->columnSpanFull()
                            ->color('danger'),
                        TextEntry::make('error_details')
                            ->label('Error Details')
                            ->columnSpanFull()
                            ->formatStateUsing(fn($state) => json_encode($state, JSON_PRETTY_PRINT))
                            ->copyable(),
                    ])
                    ->visible(fn($record) => $record->status === 'failed')
                    ->collapsed(),

                Section::make('Raw Data')
                    ->schema([
                        TextEntry::make('sage_raw_data')
                            ->label('Sage Raw Data')
                            ->columnSpanFull()
                            ->formatStateUsing(fn($state) => json_encode($state, JSON_PRETTY_PRINT))
                            ->copyable(),
                        TextEntry::make('sellsy_response')
                            ->label('Sellsy Response')
                            ->columnSpanFull()
                            ->formatStateUsing(fn($state) => json_encode($state, JSON_PRETTY_PRINT))
                            ->copyable(),
                    ])
                    ->collapsed(),
            ]);
    }
}
