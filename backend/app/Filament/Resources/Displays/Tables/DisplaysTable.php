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

                BadgeColumn::make('connection_status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        if ($record->isConnected()) {
                            return 'online';
                        } elseif ($record->status === 'connected' && $record->last_seen) {
                            return 'stale';
                        } else {
                            return 'offline';
                        }
                    })
                    ->colors([
                        'success' => 'online',
                        'warning' => 'stale', 
                        'danger' => 'offline',
                    ])
                    ->icons([
                        'heroicon-s-wifi' => 'online',
                        'heroicon-s-clock' => 'stale',
                        'heroicon-s-no-symbol' => 'offline',
                    ])
                    ->formatStateUsing(function ($state, $record) {
                        return match($state) {
                            'online' => 'Online',
                            'stale' => 'Stale (' . $record->last_seen?->diffForHumans() . ')',
                            'offline' => 'Offline',
                            default => 'Unknown'
                        };
                    })
                    ->tooltip(function ($record) {
                        if ($record->isConnected()) {
                            return 'Display is currently connected and active. Last seen: ' . $record->last_seen?->diffForHumans();
                        } elseif ($record->last_seen) {
                            return 'Display was last seen: ' . $record->last_seen->format('Y-m-d H:i:s') . ' (' . $record->last_seen->diffForHumans() . ')';
                        } else {
                            return 'Display has never connected';
                        }
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('last_seen', $direction);
                    }),

                TextColumn::make('last_seen')
                    ->label('Last Seen')
                    ->dateTime('M j, H:i')
                    ->sortable()
                    ->placeholder('Never')
                    ->tooltip(fn ($record) => $record->last_seen ? 
                        'Last seen: ' . $record->last_seen->format('Y-m-d H:i:s') . ' (' . $record->last_seen->diffForHumans() . ')' : 
                        'Display has never connected'
                    ),

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

                SelectFilter::make('connection_status')
                    ->label('Connection Status')
                    ->options([
                        'online' => 'Online (Active)',
                        'stale' => 'Stale (Inactive)', 
                        'offline' => 'Offline',
                    ])
                    ->query(function ($query, $data) {
                        if (!$data['value']) return $query;
                        
                        return match($data['value']) {
                            'online' => $query->where('status', 'connected')
                                             ->where('last_seen', '>=', now()->subMinutes(5)),
                            'stale' => $query->where('status', 'connected')
                                            ->where('last_seen', '<', now()->subMinutes(5))
                                            ->whereNotNull('last_seen'),
                            'offline' => $query->where(function($q) {
                                $q->where('status', '!=', 'connected')
                                  ->orWhereNull('last_seen');
                            }),
                            default => $query
                        };
                    }),
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
                            
                            \Log::info("About to broadcast DisplayTestMessage event");

                            // Broadcast test message to specific display channel
                            broadcast(new DisplayTestMessage($record->id, $message, $record->name));
                            
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
                    ->visible(fn($record) => $record->isConnected()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
