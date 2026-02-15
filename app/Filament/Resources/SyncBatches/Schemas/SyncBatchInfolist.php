<?php

namespace App\Filament\Resources\SyncBatches\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SyncBatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Batch Information')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Batch ID'),
                        TextEntry::make('type')
                            ->badge(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'gray',
                                'running' => 'warning',
                                'paused' => 'info',
                                'completed' => 'success',
                                'failed' => 'danger',
                            }),
                        TextEntry::make('batch_size')
                            ->label('Batch Size'),
                    ])
                    ->columns(4),

                Section::make('Statistics')
                    ->schema([
                        TextEntry::make('total_items')
                            ->label('Total Products')
                            ->icon('heroicon-o-cube'),
                        TextEntry::make('processed_items')
                            ->label('Processed')
                            ->icon('heroicon-o-check-circle')
                            ->color('info'),
                        TextEntry::make('successful_items')
                            ->label('Successful')
                            ->icon('heroicon-o-check-badge')
                            ->color('success'),
                        TextEntry::make('failed_items')
                            ->label('Failed')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger'),
                        TextEntry::make('skipped_items')
                            ->label('Skipped')
                            ->icon('heroicon-o-minus-circle')
                            ->color('warning'),
                        TextEntry::make('progress_percentage')
                            ->label('Progress')
                            ->suffix('%')
                            ->icon('heroicon-o-chart-bar')
                            ->color('info'),
                    ])
                    ->columns(3),

                Section::make('Timing')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        TextEntry::make('started_at')
                            ->label('Started At')
                            ->dateTime()
                            ->placeholder('Not started yet'),
                        TextEntry::make('completed_at')
                            ->label('Completed At')
                            ->dateTime()
                            ->placeholder('Not completed yet'),
                    ])
                    ->columns(3),
            ]);
    }
}
