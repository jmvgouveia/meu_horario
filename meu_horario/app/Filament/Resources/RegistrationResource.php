<?php

namespace App\Filament\Resources;

use App\Filament\Imports\RegistrationImporter;
use App\Filament\Resources\RegistrationResource\Pages;
use App\Filament\Resources\RegistrationResource\RelationManagers;
use App\Models\Classes;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $navigationGroup = 'Área do Aluno';
    protected static ?string $navigationLabel = 'Matrículas';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 2;

    public static function getLabel(): string
    {
        return 'Matrícula';
    }

    public static function getPluralLabel(): string
    {
        return 'Matrículas';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_schoolyear')
                    ->label('Ano Letivo')
                    ->relationship('schoolyear', 'schoolyear')
                    ->required()
                    ->default(fn() => DB::table('schoolyears')->where('active', true)->value('id'))
                    ->placeholder('Selecione o ano letivo'),
                Select::make('id_student')
                    ->label('Aluno')
                    ->required()
                    ->reactive()
                    ->searchable()
                    ->options(function () {
                        return Student::orderBy('id')
                            ->get()
                            ->mapWithKeys(fn($student) => [
                                $student->id => "{$student->number} - {$student->name}"
                            ]);
                    })
                    ->placeholder('Selecione o aluno'),
                Select::make('id_course')
                    ->label('Curso')
                    ->relationship('course', 'name')
                    ->required()
                    ->reactive()
                    ->searchable()
                    ->afterStateUpdated(fn(callable $set) => $set('id_class', null))
                    ->placeholder('Selecione o curso'),
                Select::make('id_class')
                    ->label('Turma')
                    ->required()
                    ->reactive()
                    ->options(function (callable $get) {
                        $courseId = $get('id_course');
                        if (!$courseId) return [];
                        return Classes::where('id_course', $courseId)->pluck('name', 'id');
                    })
                    ->disabled(function (callable $get) {
                        $courseId = $get('id_course');
                        if (!$courseId) return true;
                        return !Classes::where('id_course', $courseId)->exists();
                    })
                    ->hint(function (callable $get) {
                        $courseId = $get('id_course');
                        if ($courseId && !Classes::where('id_course', $courseId)->exists()) {
                            return 'Este curso não tem turmas disponíveis.';
                        }
                        return null;
                    })
                    ->placeholder('Selecione a turma'),
                Section::make('📚 Disciplinas')
                    ->description('Selecione as disciplinas associadas ao curso e turma')
                    ->schema([
                        CheckboxList::make('subjects')
                            ->relationship('subjects', 'name')
                            ->label(false)
                            ->columns(5)
                            ->columnSpanFull()
                            ->reactive()
                            ->options(function (callable $get) {
                                $courseId = $get('id_course');
                                if (!$courseId) return [];
                                return Subject::whereIn('id', function ($query) use ($courseId) {
                                    $query->select('id_subject')
                                        ->from('course_subjects')
                                        ->where('id_course', $courseId);
                                })->pluck('name', 'id');
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.number')
                    ->label('Nº Aluno')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('course.name')
                    ->label('Curso')
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('class.name')
                    ->label('Turma')
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('schoolyear.schoolyear')
                    ->label('Ano Letivo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('subjects_list')
                    ->label('Disciplinas')
                    ->getStateUsing(function ($record) {
                        return $record->subjects->pluck('name')->join(', ');
                    })
                    ->wrap()
                    ->sortable(false)
                    ->searchable(false)
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
                    ->importer(RegistrationImporter::class)
                    ->label('Importar Matrículas')
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
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}
