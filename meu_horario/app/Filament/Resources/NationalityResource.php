<?php

namespace App\Filament\Resources;

use App\Filament\Imports\NationalityImporter;
use App\Filament\Resources\NationalityResource\Pages;
use App\Filament\Resources\NationalityResource\RelationManagers;
use App\Models\Nationality;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NationalityResource extends Resource
{
    protected static ?string $model = Nationality::class;

    protected static ?string $navigationGroup = 'Gestão';
    protected static ?string $navigationLabel = 'Nacionalidades';
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nacionalidade')
                    ->required()
                    ->maxLength(255)
                    ->required()
                    ->placeholder('Introduza nacionalidade'),
                TextInput::make('acronym')
                    ->label('Sigla')
                    ->required()
                    ->maxLength(20)
                    ->placeholder('Ex: ABC'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nacionalidade')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('acronym')
                    ->label('Sigla')
                    ->sortable()
                    ->searchable(),
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
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(NationalityImporter::class)
                    ->label('Importar Nacionalidades')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('forest_green'),
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
            'index' => Pages\ListNationalities::route('/'),
            'create' => Pages\CreateNationality::route('/create'),
            'edit' => Pages\EditNationality::route('/{record}/edit'),
        ];
    }
}
