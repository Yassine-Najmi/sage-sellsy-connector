<?php

namespace App\Filament\Resources\ProductSyncs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductSyncForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sync_batch_id')
                    ->relationship('syncBatch', 'id')
                    ->required(),
                TextInput::make('ar_ref')
                    ->required(),
                Textarea::make('ar_design')
                    ->columnSpanFull(),
                TextInput::make('ar_prixven')
                    ->numeric(),
                TextInput::make('ar_prixach')
                    ->numeric(),
                TextInput::make('ta_code'),
                TextInput::make('ta_taux')
                    ->numeric(),
                TextInput::make('sage_raw_data'),
                TextInput::make('sellsy_product_id'),
                TextInput::make('sellsy_category_id'),
                TextInput::make('sellsy_tax_id'),
                TextInput::make('sellsy_response'),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'processing' => 'Processing',
            'success' => 'Success',
            'failed' => 'Failed',
            'skipped' => 'Skipped',
        ])
                    ->default('pending')
                    ->required(),
                TextInput::make('retry_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('max_retries')
                    ->required()
                    ->numeric()
                    ->default(3),
                DateTimePicker::make('last_attempt_at'),
                Textarea::make('error_message')
                    ->columnSpanFull(),
                TextInput::make('error_details'),
                TextInput::make('http_status_code')
                    ->numeric(),
            ]);
    }
}
