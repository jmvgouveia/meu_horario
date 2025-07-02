<?php

namespace App\Filament\Resources;

use App\Filament\Imports\ContratualRelationshipImporter;
use App\Filament\Resources\ContratualRelationshipResource\Pages;
use App\Models\ContratualRelationship;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContratualRelationshipResource extends Resource
{
    protected static ?string $model = ContratualRelationship::class;

    protected static ?string $navigationGroup = 'Área do Professor';
    protected static ?string $navigationLabel = 'Relações Contratuais';
    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';
    protected static ?int $navigationSort = 9;

    public static function getLabel(): string
    {
        return 'Relação Contratual';
    }

    public static function getPluralLabel(): string
    {
        return 'Relações Contratuais';
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
                    ->label('Relação Contratual')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
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
                    ->importer(ContratualRelationshipImporter::class)
                    ->label('Importar Relações Contratuais')
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
            'index' => Pages\ListContratualRelationships::route('/'),
            'create' => Pages\CreateContratualRelationship::route('/create'),
            'edit' => Pages\EditContratualRelationship::route('/{record}/edit'),
        ];
    }
}
