<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolyearResource\Pages;
use App\Models\SchoolYear;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class SchoolYearResource extends Resource
{
    protected static ?string $model = SchoolYear::class;

    protected static ?string $navigationGroup = 'Calendarização';
    protected static ?string $navigationLabel = 'Anos Lectivos';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?int $navigationSort = 3;

    public static function getLabel(): string
    {
        return 'Ano Lecivo';
    }

    public static function getPluralLabel(): string
    {
        return 'Anos Letivos';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Ano Letivo')
                    ->description('Defina o ano letivo')
                    ->schema([
                        TextInput::make('schoolyear')
                            ->label('Ano Letivo')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Toggle::make('active')
                            ->label('Ativo')
                            ->columnSpan(3),


                    ])->columns(2),

                Section::make('Marcação de Horários')
                    ->description('Defina as datas de início e fim para a marcação de horários')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Data de Início')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(
                                function ($state, callable $set, callable $get) {
                                    $startYear = Carbon::parse($state)->year;
                                    $end = $get('end_date');

                                    if ($end) {
                                        $endYear = Carbon::parse($end)->year;

                                        if ($startYear === $endYear) {

                                            throw ValidationException::withMessages([
                                                'end_date' => 'As datas devem estar em anos diferentes.',
                                            ]);
                                        }
                                    }
                                }
                            ),
                        DatePicker::make('end_date')
                            ->label('Data de Fim')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(
                                function ($state, callable $set, callable $get) {
                                    $endYear = Carbon::parse($state)->year;
                                    $start = $get('start_date');

                                    if ($start) {
                                        $startYear = Carbon::parse($start)->year;

                                        if ($startYear === $endYear) {

                                            throw ValidationException::withMessages([
                                                'end_date' => 'As datas devem estar em anos diferentes.',
                                            ]);
                                        }
                                    }
                                }
                            ),

                    ])->columns(2),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('schoolyear')
                    ->label('Ano letivo'),
                TextColumn::make('start_date')
                    ->label('Data de Início'),
                TextColumn::make('end_date')
                    ->label('Data de fim'),
                ToggleColumn::make('active')
                    ->label('Ativo')
                    ->disabled()
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
            'index' => Pages\ListSchoolYears::route('/'),
            'create' => Pages\CreateSchoolYear::route('/create'),
            'edit' => Pages\EditSchoolYear::route('/{record}/edit'),
        ];
    }
}
