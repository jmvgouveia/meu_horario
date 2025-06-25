<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleConflictResource\Pages;

use App\Models\ScheduleConflict;
use App\Models\ScheduleRequest;
use App\Models\Teacher;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\MultiSelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleConflictResource extends Resource
{
    protected static ?string $model = ScheduleRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Calendarização';
    protected static ?string $navigationLabel = 'Gestão de Conflitos';

    public static function form(Form $form): Form
    {

        return $form->schema([

            Section::make('🟢 Passo 1: Marcação original')
                ->description('O professor que fez a marcação inicial no horário.')
                ->schema([
                    Placeholder::make('professor_original')
                        ->label('Marcado por:')
                        ->content(fn($record) => $record->scheduleConflict->teacher->name ?? '—'),

                    Placeholder::make('sala')
                        ->label('Sala')
                        ->content(fn($record) => $record->scheduleConflict->room->name ?? '—'),

                    Placeholder::make('dia')
                        ->label('Dia da Semana')
                        ->content(fn($record) => $record->scheduleConflict->weekday->weekday ?? '—'),

                    Placeholder::make('hora')
                        ->label('Hora')
                        ->content(fn($record) => $record->scheduleConflict->timePeriod->description ?? '—'),
                ])
                ->columns(2),

            Section::make('🟡 Passo 2: Pedido de alteração')
                ->description('Solicitação feita por outro professor.')
                ->schema([
                    Placeholder::make('solicitante')
                        ->label('Pedido feito por:')
                        ->content(fn($record) => $record->requester->name ?? '—'),

                    Placeholder::make('data_pedido')
                        ->label('Data do Pedido')
                        ->content(fn($record) => optional($record->created_at)->format('d/m/Y H:i') ?? '—'),

                    Placeholder::make('justification')
                        ->label('Justificação do Pedido')
                        ->content(fn($record) => $record->justification ?? '—')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('🔵 Passo 3: Resposta do professor original')
                ->description('Resposta ao pedido.')
                ->schema([
                    Placeholder::make('professor_respondeu')
                        ->label('Resposta de:')
                        ->content(fn($record) => $record->scheduleConflict->teacher->name ?? '—'),

                    Placeholder::make('responded_at')
                        ->label('Data da Resposta')
                        ->content(fn($record) => optional($record->responded_at)->format('d/m/Y H:i') ?? '—'),

                    Placeholder::make('response')
                        ->label('Resposta do Professor')
                        ->content(fn($record) => $record->response ?? '—')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('🔴 Passo 4: Escalada para Direção Pedagógica')
                ->description('Situação escalada para análise superior.')
                ->schema([

                    Placeholder::make('solicitante')
                        ->label('Pedido feito por:')
                        ->content(fn($record) => $record->requester->name ?? '—'),

                    Placeholder::make('status')
                        ->label('Estado Atual')
                        ->content(fn($record) => $record->status ?? '—'),

                    Placeholder::make('justification_escalada')
                        ->label('Justificação para Escalada')
                        ->content(fn($record) => $record->justification_escalada ?? '—')
                        ->visible(fn($record) => $record->status === 'Escalado')
                        ->columnSpanFull(),
                ])
                ->columns(2),


        ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([

                // 1. Quem fez a marcação original (professor com conflito)
                TextColumn::make('scheduleConflict.teacher.name')
                    ->label('Professor com Marcações')
                    ->sortable()
                    ->searchable(),

                // 2. Quem fez o pedido
                TextColumn::make('requester.name')
                    ->label('Solicitante')
                    ->sortable()
                    ->searchable(),

                // 3. Sala
                TextColumn::make('scheduleConflict.room.name')
                    ->label('Sala')
                    ->sortable()
                    ->searchable(),

                // 4. Hora
                TextColumn::make('scheduleConflict.timePeriod.description')
                    ->label('Hora')
                    ->sortable(),

                // 5. Dia da semana
                TextColumn::make('scheduleConflict.weekday.weekday')
                    ->label('Dia da Semana')
                    ->sortable(),

                // 6. Estado
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pendente' => 'warning',
                        'Aprovado' => 'success',
                        'Recusado' => 'danger',
                        'Escalado' => 'info',
                        'Aprovado DP' => 'primary',
                        'Recusado DP' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ]) // vamos preencher depois
            ->filters([])
            ->actions([])
            ->bulkActions([]);
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
            'index' => Pages\ListScheduleConflicts::route('/'),
            'create' => Pages\CreateScheduleConflict::route('/create'),
            'edit' => Pages\EditScheduleConflict::route('/{record}/edit'),
        ];
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     $userId = Filament::auth()->id();
    //     $user = Filament::auth()->user();

    //     if ($user instanceof \App\Models\User && $user->hasRole('Super Admin')) {
    //         return parent::getEloquentQuery();
    //     }

    //     $teacher = Teacher::where('id_user', $userId)->first();

    //     return parent::getEloquentQuery()
    //         ->where(function ($query) use ($teacher) {
    //             $query
    //                 ->where('id_teacher_requester', $teacher?->id)
    //                 ->orWhereHas('scheduleConflict', function ($subQuery) use ($teacher) {
    //                     $subQuery->where('id_teacher', $teacher?->id);
    //                 });
    //         });
    // }


}
