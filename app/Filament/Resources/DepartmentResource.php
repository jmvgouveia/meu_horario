<?php

namespace App\Filament\Resources;

use App\Filament\Imports\DepartmentImporter;
use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationGroup = 'Área do Professor';
    protected static ?string $navigationLabel = 'Departamentos Curriculares';
    protected static ?string $navigationIcon = 'heroicon-s-briefcase';
    protected static ?int $navigationSort = 3;

    public static function getLabel(): string
    {
        return 'Departamento Curricular';
    }

    public static function getPluralLabel(): string
    {
        return 'Departamentos Curriculares';
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
                Textarea::make('description')
                    ->label('Descrição')
                    ->maxLength(255)
                    ->placeholder('Introduza descrição')
                    ->columnSpanFull(),
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
                    ->sortable()
                    ->searchable()
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(DepartmentImporter::class)
                    ->label('Importar Departamentos')
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
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
