<?php

namespace App\Filament\Resources\SyncBatches\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SyncBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('type')
                    ->required()
                    ->default('product_sync'),
                TextInput::make('total_items')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('processed_items')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('successful_items')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('failed_items')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('skipped_items')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'running' => 'Running',
            'completed' => 'Completed',
            'failed' => 'Failed',
        ])
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
                TextInput::make('current_offset')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('batch_size')
                    ->required()
                    ->numeric()
                    ->default(100),
                TextInput::make('config'),
            ]);
    }
}
