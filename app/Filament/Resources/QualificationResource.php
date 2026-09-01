<?php

namespace App\Filament\Resources;

use App\Filament\Imports\QualificationImporter;
use App\Filament\Resources\QualificationResource\Pages;
use App\Models\Qualification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QualificationResource extends Resource
{
    protected static ?string $model = Qualification::class;

    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $navigationLabel = 'Qualificações';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 5;

    public static function getLabel(): string
    {
        return 'Habilitação Académica';
    }

    public static function getPluralLabel(): string
    {
        return 'Habilitações Académicas';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Habilitação Académica')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Introduza a habilitação académica')
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(3)
                    ->maxLength(1000)
                    ->placeholder('Introduza uma descrição')
                    ->columnSpanFull(),

                Select::make('qnq_level')
                    ->label('Nível QNQ')
                    ->options([
                        1 => 'Nível 1',
                        2 => 'Nível 2',
                        3 => 'Nível 3',
                        4 => 'Nível 4',
                        5 => 'Nível 5',
                        6 => 'Nível 6',
                        7 => 'Nível 7',
                        8 => 'Nível 8',
                    ])
                    ->placeholder('Sem nível QNQ')
                    ->nullable(),

                TextInput::make('sort_order')
                    ->label('Ordem')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),

                Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true)
                    ->inline(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Habilitação Académica')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(60)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState())
                    ->toggleable(),

                TextColumn::make('qnq_level')
                    ->label('Nível QNQ')
                    ->formatStateUsing(
                        fn ($state): string => $state
                            ? "Nível {$state}"
                            : '—'
                    )
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Ordem')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('qnq_level')
                    ->label('Nível QNQ')
                    ->options([
                        1 => 'Nível 1',
                        2 => 'Nível 2',
                        3 => 'Nível 3',
                        4 => 'Nível 4',
                        5 => 'Nível 5',
                        6 => 'Nível 6',
                        7 => 'Nível 7',
                        8 => 'Nível 8',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Ativos')
                    ->falseLabel('Inativos')
                    ->placeholder('Todos'),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(QualificationImporter::class)
                    ->label('Importar Qualificações')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('forest_green'),
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
