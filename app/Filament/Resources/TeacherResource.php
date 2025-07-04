<?php

namespace App\Filament\Resources;

use App\Filament\Imports\TeacherImporter;
use App\Filament\Resources\TeacherResource\Pages;
use App\Helpers\ValidationRules;
use App\Models\Teacher;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationGroup = 'Área do Professor';
    protected static ?string $navigationLabel = 'Professores';
    protected static ?string $navigationIcon = 'heroicon-s-users';
    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return 'Professor';
    }

    public static function getPluralLabel(): string
    {
        return 'Professores';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados pessoais')
                    ->description('Dados pessoais do professor')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Introduza nome')
                            ->columnSpan(3),
                        DatePicker::make('birthdate')
                            ->label('Data de nascimento')
                            ->before(Carbon::now()->subYears(18))
                            ->validationMessages([
                                'before' => 'Tem de ter pelo menos 18 anos de idade.',
                            ])
                            ->required(),
                        Select::make('id_gender')
                            ->label('Género')
                            ->relationship('gender', 'gender')
                            ->placeholder('Selecione o género'),
                        Select::make('id_nationality')
                            ->relationship('nationality', 'name')
                            ->label('Nacionalidade')
                            ->placeholder('Selecione o Nacionalidade'),
                    ])->columns(3),
                Section::make('Dados professor')
                    ->description('Dados de professor')
                    ->schema([
                        TextInput::make('number')
                            ->label('Número de professor')
                            ->required()
                            ->numeric()
                            ->placeholder('Introduza número de professor'),
                        TextInput::make('acronym')
                            ->label('Sigla')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('Introduza sigla'),
                        DatePicker::make('startingdate')
                            ->label('Data de início de funções')
                            ->required()
                            ->placeholder('Selecione a data de inicio de funções')
                            ->rule(function (Get $get) {
                                $birthdate = $get('birthdate');
                                if (! $birthdate) return null;

                                $minDate = Carbon::parse($birthdate)->addYears(18)->toDateString();
                                return 'after_or_equal:' . $minDate;
                            })
                            ->validationMessages([
                                'after_or_equal' => 'A data de início deve ser pelo menos 18 anos após a data de nascimento.',
                            ]),
                        Select::make('id_qualification')
                            ->relationship('qualification', 'name')
                            ->label('Habilitações')
                            ->placeholder('Selecione a Habilitação'),
                        Select::make('id_department')
                            ->relationship('department', 'name')
                            ->label('Departamento')
                            ->placeholder('Selecione a departamento'),
                        Select::make('id_professionalrelationship')
                            ->relationship('professionalrelationship', 'name')
                            ->label('Relação Profissional')
                            ->placeholder('Selecione a Relação Profissional'),
                        Select::make('id_contractualrelationship')
                            ->relationship('contractualrelationship', 'name')
                            ->label('Relação Contratual')
                            ->placeholder('Selecione a Relação Contratual'),
                        Select::make('id_salaryscale')
                            ->relationship('salaryscale', 'scale')
                            ->label('Escalão Salarial')
                            ->placeholder('Selecione a Escalão Salarial'),
                        Select::make('positions')
                            ->label('Cargos')
                            ->relationship('positions', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Select::make('time_reductions')
                            ->label('Reduções de Horário')
                            ->relationship('timeReductions', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),

                    ]),
                Section::make('Dados utilizador')
                    ->description('Dados de utilizador')
                    ->schema([
                        TextInput::make('user.email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->placeholder('Introduza e-mail'),
                        TextInput::make('user.password')
                            ->label('Password')
                            ->nullable()
                            ->required(fn(Get $get) => request()->routeIs('filament.admin.resources.teachers.create'))
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->minLength(5)
                            ->placeholder('Deixe em branco para manter a atual')
                            ->regex(ValidationRules::PASSWORD_REGEX)
                            ->helperText(ValidationRules::PASSWORD_HELPER_MSG),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Número')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('acronym')
                    ->label('Sigla')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('department.name')
                    ->label('Departamento')
                    ->wrap()
                    ->sortable()
                    ->searchable()
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
                    ->importer(TeacherImporter::class)
                    ->label('Importar Professores')
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
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
