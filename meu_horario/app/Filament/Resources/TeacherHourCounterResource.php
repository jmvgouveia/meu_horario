<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherHourCounterResource\Pages;
use App\Filament\Resources\TeacherHourCounterResource\RelationManagers;
use App\Models\TeacherHourCounter;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class TeacherHourCounterResource extends Resource
{
    protected static ?string $model = TeacherHourCounter::class;

    protected static ?string $navigationGroup = 'Área do Professor';
    protected static ?string $navigationLabel = 'Carga Horária';
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_teacher')
                    ->label('Professor')
                    ->relationship('teacher', 'name')
                    ->required()
                    ->reactive()
                    ->placeholder('Selecione um professor'),
                Checkbox::make('authorized_overtime')
                    ->label('Horas Extras Autorizadas')
                    ->default(false)
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('teaching_load')
                    ->label('Carga Horária Letiva')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(function (callable $get) {
                        Log::info('Valor de authorized_overtime', ['valor' => $get('authorized_overtime')]);

                        return $get('authorized_overtime') === true ? 27 : 22;
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('workload', ($state ?? 0) + ($get('non_teaching_load') ?? 0));
                    })
                    ->placeholder('Introduza carga horária letiva'),
                TextInput::make('non_teaching_load')
                    ->label('Carga Horária Não Letiva')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(4)
                    ->maxLength(1)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('workload', ($get('teaching_load') ?? 0) + ($state ?? 0));
                    })
                    ->placeholder('Introduza carga horária não letiva'),
                TextInput::make('workload')
                    ->label('Carga Horária Total')
                    ->numeric()
                    ->disabled()
                    ->reactive()
                    ->dehydrated(true)
                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                        $component->state(
                            ($record->teaching_load ?? 0) + ($record->non_teaching_load ?? 0)
                        );
                    }),
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
            'index' => Pages\ListTeacherHourCounters::route('/'),
            'create' => Pages\CreateTeacherHourCounter::route('/create'),
            'edit' => Pages\EditTeacherHourCounter::route('/{record}/edit'),
        ];
    }
}
