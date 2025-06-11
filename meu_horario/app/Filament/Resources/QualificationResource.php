<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QualificationResource\Pages;
use App\Filament\Resources\QualificationResource\RelationManagers;
use App\Models\Qualification;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QualificationResource extends Resource
{
    protected static ?string $model = Qualification::class;

    protected static ?string $navigationGroup = 'Gestão';
    protected static ?string $navigationLabel = 'Qualificações';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Habilitação')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Introduza habilitação'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Habilitação')
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
            'index' => Pages\ListQualifications::route('/'),
            'create' => Pages\CreateQualification::route('/create'),
            'edit' => Pages\EditQualification::route('/{record}/edit'),
        ];
    }
}
