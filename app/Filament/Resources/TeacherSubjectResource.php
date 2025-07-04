<?php

namespace App\Filament\Resources;

use App\Filament\Imports\TeacherSubjectsImporter;
use App\Filament\Resources\TeacherSubjectResource\Pages;
use App\Models\SchoolYear;
use App\Models\TeacherSubject;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


class TeacherSubjectResource extends Resource
{
    protected static ?string $model = TeacherSubject::class;

    protected static ?string $navigationGroup = 'Área do Professor';
    protected static ?string $navigationLabel = 'Professores - Disciplinas';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?int $navigationSort = 2;

    // public static function getLabel(): string
    // {
    //     return 'Disciplina do Professor';
    // }

    public static function getPluralLabel(): string
    {
        return auth()->user()?->isSuperAdmin()
            ? 'Disciplinas do Professor'
            : 'As minhas disciplinas';
    }
    public static function getNavigationLabel(): string
    {
        return auth()->user()?->isSuperAdmin()
            ? 'Professores - Disciplinas'
            : 'As minhas disciplinas';
    }


    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        // Admin vê tudo (assumindo que há um método isAdmin())
        if ($user->isSuperAdmin()) {
            return $query;
        }

        // Se não for admin, filtra pelo professor logado e ano letivo ativo
        $activeYear = SchoolYear::where('active', true)->first();

        if ($activeYear) {
            return $query
                ->where('id_teacher', $user->teacher->id ?? null)
                ->where('id_schoolyear', $activeYear->id);
        }

        // Se não houver ano letivo ativo, retorna query vazia
        return $query->whereRaw('1 = 0');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_schoolyear')
                    ->label('Ano Letivo')
                    ->required()
                    ->relationship('schoolyear', 'schoolyear')
                    ->placeholder('Selecione o ano letivo'),
                Select::make('id_teacher')
                    ->label('Professor')
                    ->required()
                    ->relationship('teacher', 'name')
                    ->placeholder('Selecione o professor'),
                Select::make('id_subject')
                    ->label('Disciplina')
                    ->required()
                    ->relationship('subject', 'name')
                    ->placeholder('Selecione a disciplina'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('schoolyear.schoolyear')
                    ->label('Ano Lectivo')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('teacher.name')
                    ->label('Professor')
                    ->sortable()
                    ->searchable()
                    ->visible(fn() => auth()->user()?->hasRole('Super Admin')),
                TextColumn::make('subject.name')
                    ->label('Disciplina')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_schoolyear')
                    ->label('Ano Letivo')
                    ->relationship('schoolyear', 'schoolyear')
                    ->searchable()
                    ->preload(),

                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(TeacherSubjectsImporter::class)
                    ->label('Importar Disciplinas-Professor')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('forest_green')
                    ->visible(fn() => auth()->user()?->hasRole('Super Admin')),

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
            'index' => Pages\ListTeacherSubjects::route('/'),
            'create' => Pages\CreateTeacherSubject::route('/create'),
            'edit' => Pages\EditTeacherSubject::route('/{record}/edit'),
        ];
    }
}
