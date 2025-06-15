<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfessionalRelationshipResource\Pages;
use App\Filament\Resources\ProfessionalRelationshipResource\RelationManagers;
use App\Models\ProfessionalRelationship;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProfessionalRelationshipResource extends Resource
{
    protected static ?string $model = ProfessionalRelationship::class;

    protected static ?string $navigationGroup = 'Área do Professor';
    protected static ?string $navigationLabel = 'Relações Profissionais';
    protected static ?string $navigationIcon = 'heroicon-s-bookmark-square';
    protected static ?int $navigationSort = 10;

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
