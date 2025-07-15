<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomBlockedHoursResource\Pages;
use App\Filament\Resources\RoomBlockedHoursResource\RelationManagers;
use App\Models\Room;
use App\Models\RoomBlockedHours;
use App\Models\Schedule;
use App\Models\Weekday;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomBlockedHoursResource extends Resource
{
    protected static ?string $model = RoomBlockedHours::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Calendarização';
    protected static ?string $navigationLabel = 'Bloqueio de Salas';

    protected static ?int $navigationSort = 6;

    public static function getModelLabel(): string
    {
        return 'Bloqueio de Sala';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Bloqueios de Salas';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_building')
                    ->label('Núcleo/Polo')
                    ->relationship('building', 'name')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('id_room', null))
                    ->preload(),

                Select::make('id_room')
                    ->label('Sala')
                    ->required()
                    ->disabled(fn(callable $get) => blank($get('id_building')))
                    ->placeholder('Tem que preencher o Núcleo/Pólo')
                    ->options(function (callable $get, ?RoomBlockedHours $record) {
                        $buildingId = $get('id_building') ?? $record?->room?->id_building;
                        if (!$buildingId) return [];
                        return Room::where('id_building', $buildingId)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->reactive()
                    ->afterStateHydrated(function (callable $set, ?RoomBlockedHours $record) {
                        if ($record && $record->id_room) {
                            $set('id_room', $record->id_room);
                        }
                    }),
                Select::make('id_weekday')
                    ->label('Dia da Semana')
                    ->options(
                        Weekday::orderBy('id')->pluck('weekday', 'id')->toArray()
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('id_timeperiod')
                    ->label('Período de Tempo')
                    ->relationship('timeperiod', 'description')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('id_schoolyear')
                    ->label('Ano Letivo')
                    ->relationship('schoolyear', 'schoolyear')
                    ->required()
                    ->searchable()
                    ->preload(),
                Textarea::make('description')
                    ->label('Observação')
                    ->maxLength(255)
                    ->nullable(),
                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true)
                    ->inline(false)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                    ->label('Núcleo/Pólo')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('room.name')
                    ->label('Sala')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('weekday.weekday')
                    ->label('Dia da Semana')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('timeperiod.description')
                    ->label('Período de Tempo')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('schoolyear.schoolyear')
                    ->label('Ano Letivo')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Observação')
                    ->limit(50)
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                ToggleColumn::make('active')
                    ->label('Ativo')
                    ->sortable()
                    ->searchable(),
                //
            ])
            ->filters([

                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListRoomBlockedHours::route('/'),
            'create' => Pages\CreateRoomBlockedHours::route('/create'),
            'edit' => Pages\EditRoomBlockedHours::route('/{record}/edit'),
        ];
    }
}
