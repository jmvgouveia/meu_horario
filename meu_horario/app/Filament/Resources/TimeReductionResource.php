<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimeReductionResource\Pages;
use App\Filament\Resources\TimeReductionResource\RelationManagers;
use App\Models\TimeReduction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TimeReductionResource extends Resource
{
    protected static ?string $model = TimeReduction::class;

    protected static ?string $navigationGroup = 'Calendarização';
    protected static ?string $navigationLabel = 'Reduções de Horário';
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->maxLength(255)
                    ->required()
                    ->placeholder('Introduza nome'),
                TextInput::make('description')
                    ->label('Descrição')
                    ->maxLength(255)
                    ->placeholder('Introduza descrição')
                    ->columnSpanFull(),
                TextInput::make('value_l')
                    ->label('Redução Letiva')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(99)
                    ->placeholder('Introduza redução letiva')
                    ->helperText('Ex: 1'),
               TextInput::make('value_nl')
                    ->label('Redução Não Letiva')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(99)
                    ->placeholder('Introduza redução não letiva')
                    ->helperText('Ex: 1'),
                Select::make('eligibility')
                    ->label('Elegibilidade')
                    ->required()
                    ->options([
                        'Ambos' => 'Ambos',
                        'Masculino' => 'Masculino',
                        'Feminino' => 'Feminino',
                    ])
                    ->default('Ambos'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Descrição')
                    ->wrap()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('value_l')
                    ->label('Redução Letiva')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('value_nl')
                    ->label('Redução Não Letiva')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('eligibility') 
                    ->label('Elegibilidade')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'feminino' => 'primary',
                        'masculino' => 'success',
                        'ambos' => 'info',
                    })
                    ->sortable()
                    ->toggleable(),
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
            'index' => Pages\ListTimeReductions::route('/'),
            'create' => Pages\CreateTimeReduction::route('/create'),
            'edit' => Pages\EditTimeReduction::route('/{record}/edit'),
        ];
    }
}
