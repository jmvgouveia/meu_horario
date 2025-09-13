<?php

namespace App\Filament\Resources;

use App\Filament\Imports\TeacherHourCounterImporter;
use App\Filament\Resources\TeacherHourCounterResource\Pages;
use App\Models\TeacherHourCounter;
use Dom\Text;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\ToggleColumn;

class TeacherHourCounterResource extends Resource
{
    protected static ?string $model = TeacherHourCounter::class;

    protected static ?string $navigationGroup = 'Área do Professor';
    protected static ?string $navigationLabel = 'Carga Horária';
    protected static ?string $navigationIcon = 'heroicon-s-clock';
    protected static ?int $navigationSort = 8;

    public static function getLabel(): string
    {
        return 'Carga Horária do Professor';
    }

    public static function getPluralLabel(): string
    {
        return 'Cargas Horárias dos Professores';
    }

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
                // Toggle::make('authorized_overtime')
                //     ->label('Horas Extras Autorizadas')
                //     ->default(false)
                //     ->columnSpanFull(),

                Toggle::make('authorized_overtime')
                    ->label('Horas Extras Autorizadas')
                    ->default(false)
                    ->live() // necessário para atualizar a UI no toggle
                    ->columnSpanFull()
                    ->afterStateUpdated(function (bool $state, Set $set) {
                        if (! $state) {
                            // se desligar, força o valor para 0
                            $set('numOvertime', 0);
                        }
                    }),

                TextInput::make('numovertime')
                    ->label('Horas Extras (h)')
                    ->numeric()
                    ->inputMode('decimal')
                    ->minValue(0)
                    ->step('0.25')              // ajusta se quiseres 0.5 ou 0.01
                    ->suffix('h')
                    ->default(0)
                    ->visible(fn(Get $get) => (bool) $get('authorized_overtime'))         // só mostra quando autorizado
                    ->required(fn(Get $get) => (bool) $get('authorized_overtime'))        // obriga quando visível
                    ->dehydrateStateUsing(
                        fn($state, Get $get) =>                        // ao gravar:
                        $get('authorized_overtime') ? (float) ($state ?? 0) : 0           //   se off, guarda 0
                    ),


                TextInput::make('teaching_load')
                    ->label('Carga Letiva')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(function (callable $get) {
                        return $get('authorized_overtime') === true ? 27 : 22;
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('workload', ($state ?? 0) + ($get('non_teaching_load') ?? 0));
                    })
                    ->placeholder('Introduza carga horária letiva'),
                TextInput::make('non_teaching_load')
                    ->label('Carga Não Letiva')
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
                    ->reactive()
                    ->dehydrated(true)
                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                        $component->state(
                            ($record->teaching_load ?? 0) + ($record->non_teaching_load ?? 0) + ($record->numovertime ?? 0)
                        );
                    }),

                Select::make('id_schoolyear')
                    ->label('Ano Letivo')
                    ->relationship('schoolyear', 'schoolyear')
                    ->required()
                    ->placeholder('Selecione o ano letivo')

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('schoolyear.schoolyear')
                    ->label('Ano Letivo')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('teacher.name')
                    ->label('Professor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('workload')
                    ->label('Carga Restante')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teaching_load')
                    ->label('Carga Letiva')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('non_teaching_load')
                    ->label('Carga Não Letiva')
                    ->searchable()
                    ->sortable(),

                ToggleColumn::make('authorized_overtime')
                    ->label('Extras Autorizadas')
                    ->onColor('primary')
                    ->searchable()
                    ->disabled()
                    ->sortable(),


                TextColumn::make('numovertime')
                    ->label('Nº de Horas Extras')
                    ->searchable()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(TeacherHourCounterImporter::class)
                    ->label('Import Teacher Hours')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(TeacherHourCounterImporter::class)
                    ->label('Importar Cargas Horárias')
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
            'index' => Pages\ListTeacherHourCounters::route('/'),
            'create' => Pages\CreateTeacherHourCounter::route('/create'),
            'edit' => Pages\EditTeacherHourCounter::route('/{record}/edit'),
        ];
    }
}
