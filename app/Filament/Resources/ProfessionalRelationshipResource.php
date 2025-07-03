<?php

namespace App\Filament\Resources;

use App\Filament\Imports\ProfessionalRelationshipImporter;
use App\Filament\Resources\ProfessionalRelationshipResource\Pages;
use App\Models\ProfessionalRelationship;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProfessionalRelationshipResource extends Resource
{
    protected static ?string $model = ProfessionalRelationship::class;

    protected static ?string $navigationGroup = 'Área do Professor';
    protected static ?string $navigationLabel = 'Relações Profissionais';
    protected static ?string $navigationIcon = 'heroicon-s-bookmark-square';
    protected static ?int $navigationSort = 10;

    public static function getLabel(): string
    {
        return 'Relação Profissional';
    }

    public static function getPluralLabel(): string
    {
        return 'Relações Profissionais';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Introduza nome'),
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(ProfessionalRelationshipImporter::class)
                    ->label('Importar Relações Profissionais')
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
            'index' => Pages\ListProfessionalRelationships::route('/'),
            'create' => Pages\CreateProfessionalRelationship::route('/create'),
            'edit' => Pages\EditProfessionalRelationship::route('/{record}/edit'),
        ];
    }
}
