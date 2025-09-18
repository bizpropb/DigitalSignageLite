<?php

namespace App\Filament\Resources\Displays\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Events\DisplayTestMessage;

class DisplaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('program.name')
                    ->label('Program')
                    ->searchable()
                    ->sortable()
                    ->placeholder('No program assigned')
                    ->badge()
                    ->color('info'),

                TextColumn::make('location')
                    ->searchable()
                    ->placeholder('Not set')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'connected',
                        'danger' => 'disconnected',
                        'warning' => 'error',
                    ])
                    ->icons([
                        'heroicon-s-wifi' => 'connected',
                        'heroicon-s-no-symbol' => 'disconnected',
                        'heroicon-s-exclamation-triangle' => 'error',
                    ])
                    ->sortable(),

                TextColumn::make('access_token')
                    ->label('Access Token')
                    ->badge()
                    ->color('primary')
                    ->copyable()
                    ->copyMessage('Access token copied to clipboard')
                    ->placeholder('Not generated')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('Y-m-d')
                    ->sortable()
                    ->tooltip(fn ($record) => $record->created_at->format('Y-m-d H:i:s')),
            ])
            ->filters([
                SelectFilter::make('program_id')
                    ->label('Program')
                    ->relationship('program', 'name'),

                SelectFilter::make('status')
                    ->options([
                        'connected' => 'Connected',
                        'disconnected' => 'Disconnected',
                        'error' => 'Error',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                
                DeleteAction::make(),

                Action::make('testMessage')
                    ->label('Test')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('info')
                    ->tooltip('Send a test message to this display')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('message')
                            ->label('Message')
                            ->placeholder('Enter test message...')
                            ->default('Test message from admin panel')
                            ->required()
                            ->maxLength(255)
                    ])
                    ->modalHeading('Send Test Message')
                    ->modalDescription(fn($record) => "Send a test message to display: {$record->name}")
                    ->modalSubmitActionLabel('Send Message')
                    ->action(function ($record, $data) {
                        try {
                            $message = $data['message'];
                            
                            \Log::info("TEST MESSAGE BUTTON CLICKED - START");
                            \Log::info("Display Record:", ['id' => $record->id, 'name' => $record->name, 'auth_token' => $record->auth_token]);
                            \Log::info("Message Data:", ['message' => $message]);
                            
                            \Log::info("About to broadcast TestMessage event");

                            // Broadcast test message with display's auth_token for verification
                            broadcast(new \App\Events\TestMessage($message, $record->auth_token));
                            
                            \Log::info("Broadcast call completed successfully");

                            Notification::make()
                                ->title('Test Message Sent')
                                ->body("Message sent to display '{$record->name}': {$message}")
                                ->success()
                                ->send();
                                
                            \Log::info("TEST MESSAGE BUTTON CLICKED - END SUCCESS");
                        } catch (\Exception $e) {
                            \Log::error("TEST MESSAGE BUTTON CLICKED - ERROR:", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                            
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to send test message: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn($record) => $record->status === 'connected'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
