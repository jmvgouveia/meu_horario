<?php

namespace App\Filament\Resources;

use App\Filament\Imports\PositionImporter;
use App\Filament\Resources\PositionResource\Pages;
use App\Models\Position;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static ?string $navigationGroup = 'Área do Professor';
    protected static ?string $navigationLabel = 'Cargos';
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?int $navigationSort = 4;

    public static function getLabel(): string
    {
        return 'Cargo';
    }

    public static function getPluralLabel(): string
    {
        return 'Cargos';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->required()
                    ->placeholder('Introduza nome')
                    ->columnSpan(2),
                TextInput::make('description')
                    ->label('Descrição')
                    ->maxLength(255)
                    ->placeholder('Introduza descrição')
                    ->columnSpanFull(),
                TextInput::make('reduction_l')
                    ->label('Redução Letiva')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(99)
                    ->placeholder('Introduza redução letiva')
                    ->helperText('Ex: 1'),
                TextInput::make('reduction_nl')
                    ->label('Redução Não Letiva')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(99)
                    ->placeholder('Introduza redução não letiva')
                    ->helperText('Ex: 2'),
            ])->columns(4);
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
                    ->searchable(),
                TextColumn::make('reduction_l')
                    ->label('Redução Letiva')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('reduction_nl')
                    ->label('Redução Não Letiva')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(PositionImporter::class)
                    ->label('Importar Cargos')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('forest_green'),
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
            'index' => Pages\ListPositions::route('/'),
            'create' => Pages\CreatePosition::route('/create'),
            'edit' => Pages\EditPosition::route('/{record}/edit'),
        ];
    }
}
