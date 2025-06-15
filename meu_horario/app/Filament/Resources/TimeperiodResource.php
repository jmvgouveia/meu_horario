<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimeperiodResource\Pages;
use App\Filament\Resources\TimeperiodResource\RelationManagers;
use App\Models\Timeperiod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TimeperiodResource extends Resource
{
    protected static ?string $model = Timeperiod::class;

    protected static ?string $navigationGroup = 'Calendarização';
    protected static ?string $navigationLabel = 'Períodos de tempo';
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label("Descrição")
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                /* Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]), */
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
            //'create' => Pages\CreateTimeperiod::route('/create'),
            //'edit' => Pages\EditTimeperiod::route('/{record}/edit'),
        ];
    }
}
