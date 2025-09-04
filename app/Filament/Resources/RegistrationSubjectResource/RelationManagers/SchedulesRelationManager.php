<?php

namespace App\Filament\Resources\RegistrationSubjectResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'availableSchedules'; // nome da relação no modelo RegistrationSubject
    protected static ?string $recordTitleAttribute = 'name';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Turno'),
                Tables\Columns\TextColumn::make('day')->label('Dia'),
                Tables\Columns\TextColumn::make('hour')->label('Hora'),
                Tables\Columns\TextColumn::make('room.name')->label('Sala'),
            ])
            ->actions([
                Action::make('chooseShift')
                    ->label('Escolher')
                    ->color('primary')
                    ->action(function ($record, $data, $livewire) {
                        $livewire->getOwnerRecord()->update([
                            'id_schedule' => $record->id,
                            'shift' => $record->name,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title("Turno {$record->name} escolhido")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
