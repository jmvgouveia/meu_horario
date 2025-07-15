<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimeperiodResource\Pages;
use App\Models\Timeperiod;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TimeperiodResource extends Resource
{
    protected static ?string $model = Timeperiod::class;

    protected static ?string $navigationGroup = 'Calendarização';
    protected static ?string $navigationLabel = 'Períodos de tempo';
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?int $navigationSort = 4;

    public static function getLabel(): string
    {
        return 'Hora de Aula';
    }

    public static function getPluralLabel(): string
    {
        return 'Horas de Aula';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('id')
                    ->label('ID')
                    ->required()
                    ->numeric(),
                TextInput::make('description')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label("Descrição")
                    ->searchable(),
                TextColumn::make('start_time')
                    ->label("Hora de Início")
                    ->dateTime('H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label("Hora de Fim"),
                ToggleColumn::make('active')
                    ->label('Ativo')
                    ->default(true)
                    ->inline(false)
                    ->columnSpanFull(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTimeperiods::route('/'),
            'create' => Pages\CreateTimeperiod::route('/create'),
            'edit' => Pages\EditTimeperiod::route('/{record}/edit'),
        ];
    }
}
