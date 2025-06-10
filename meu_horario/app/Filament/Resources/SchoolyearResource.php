<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolyearResource\Pages;
use App\Filament\Resources\SchoolyearResource\RelationManagers;
use App\Models\Schoolyear;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\ValidationException;

class SchoolyearResource extends Resource
{
    protected static ?string $model = Schoolyear::class;

    protected static ?string $navigationGroup = 'Calendarização';
    protected static ?string $navigationLabel = 'Anos Lectivos';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Checkbox::make('active')
                    ->label('Ativo')
                    ->columnSpan(3),
                DatePicker::make('start_date')
                    ->label('Data de Início')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(
                        function ($state, callable $set, callable $get) {
                            $startYear = \Carbon\Carbon::parse($state)->year;
                            $end = $get('end_date');

                            if ($end) {
                                $endYear = \Carbon\Carbon::parse($end)->year;

                                if ($startYear === $endYear) {
                                    $set('schoolyear', null);
                                    throw ValidationException::withMessages([
                                        'end_date' => 'As datas devem estar em anos diferentes.',
                                    ]);
                                }

                                $set('schoolyear', "{$startYear}/{$endYear}");
                            }
                        }),
                DatePicker::make('end_date')
                    ->label('Data de Fim')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(
                        function ($state, callable $set, callable $get) {
                            $endYear = \Carbon\Carbon::parse($state)->year;
                            $start = $get('start_date');

                            if ($start) {
                                $startYear = \Carbon\Carbon::parse($start)->year;

                                if ($startYear === $endYear) {
                                    $set('schoolyear', null);
                                    throw ValidationException::withMessages([
                                        'end_date' => 'As datas devem estar em anos diferentes.',
                                    ]);
                                }

                                $set('schoolyear', "{$startYear}/{$endYear}");
                            }
                        }),

                TextInput::make('schoolyear')
                    ->label('Ano Letivo')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->unique(ignoreRecord: true),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListSchoolyears::route('/'),
            'create' => Pages\CreateSchoolyear::route('/create'),
            'edit' => Pages\EditSchoolyear::route('/{record}/edit'),
        ];
    }
}
