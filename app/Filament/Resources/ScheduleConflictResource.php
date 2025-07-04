<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleConflictResource\Pages;
use App\Models\ScheduleRequest;
use App\Models\SchoolYear;
use App\Models\Teacher;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ScheduleConflictResource extends Resource
{
    protected static ?string $model = ScheduleRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Calendarização';
    protected static ?string $navigationLabel = 'Gestão de Conflitos';

    public static function getLabel(): string
    {
        return 'Gestão de Conflitos de Horário';
    }

    public static function getPluralLabel(): string
    {
        return 'Gestão de Conflitos de Horário';
    }


    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();
        $userId = $user->id;
        $teacher = Teacher::where('id_user', $userId)->first();
        $anoLetivoAtivo = SchoolYear::where('active', true)->first();

        if (! $anoLetivoAtivo) {
            return parent::getEloquentQuery()->whereRaw('0 = 1');
        }

        // Estados válidos para visualização
        $estadosVisiveis = ['Escalado', 'Aprovado DP', 'Recusado DP'];

        // ✅ Gestor de conflito: vê tudo do ano letivo ativo com os estados definidos
        if ($user->hasRole('Gestor Conflitos')) {
            return parent::getEloquentQuery()
                ->whereIn('status', $estadosVisiveis)
                ->where(function ($query) use ($anoLetivoAtivo) {
                    $query
                        ->whereHas('scheduleNew', function ($q) use ($anoLetivoAtivo) {
                            $q->where('id_schoolyear', $anoLetivoAtivo->id);
                        })
                        ->orWhereHas('scheduleConflict', function ($q) use ($anoLetivoAtivo) {
                            $q->where('id_schoolyear', $anoLetivoAtivo->id);
                        });
                });
        }

        // ✅ Professor: vê apenas os seus pedidos com os estados e ano letivo ativos
        if (! $teacher) {
            return parent::getEloquentQuery()->whereRaw('0 = 1');
        }

        return parent::getEloquentQuery()
            ->whereIn('status', $estadosVisiveis)
            ->where(function ($query) use ($teacher) {
                $query
                    ->where('id_teacher', $teacher->id)
                    ->orWhereHas('scheduleConflict', function ($subQuery) use ($teacher) {
                        $subQuery->where('id_teacher', $teacher->id);
                    });
            })
            ->where(function ($query) use ($anoLetivoAtivo) {
                $query
                    ->whereHas('scheduleNew', function ($q) use ($anoLetivoAtivo) {
                        $q->where('id_schoolyear', $anoLetivoAtivo->id);
                    })
                    ->orWhereHas('scheduleConflict', function ($q) use ($anoLetivoAtivo) {
                        $q->where('id_schoolyear', $anoLetivoAtivo->id);
                    });
            });
    }


    public static function form(Form $form): Form
    {

        return $form->schema([

            Section::make('🟢 Passo 1: Marcação original')
                ->collapsible()
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
                ->collapsible()

                ->description('Solicitação feita por outro professor.')
                ->schema([
                    Placeholder::make('solicitante')
                        ->label('Pedido por:')
                        ->content(fn($record) => $record->requester->name ?? '—'),

                    Placeholder::make('data_pedido')
                        ->label('Data do Pedido')
                        ->content(fn($record) => optional($record->created_at)->format('d/m/Y H:i') ?? '—'),

                    Placeholder::make('justification')
                        ->label('Justificação do Pedido: ')
                        ->content(fn($record) => $record->justification ?? '—')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('🔵 Passo 3: Resposta do professor original')
                ->collapsible()

                ->description('Resposta ao pedido.')
                ->schema([
                    Placeholder::make('professor_respondeu')
                        ->label('Resposta de:')
                        ->content(fn($record) => $record->scheduleConflict->teacher->name ?? '—'),

                    Placeholder::make('responded_at')
                        ->label('Data da Resposta')
                        ->content(fn($record) => $record->responded_at
                            ? Carbon::parse($record->responded_at)->format('d/m/Y H:i')
                            : '—'),



                    Placeholder::make('response')
                        ->label('Resposta:')
                        ->content(fn($record) => $record->response ?? '—')
                        ->columnSpanFull(),
                ])
                ->columns(2),


            Section::make('🔴 Passo 4: Situação Escalada para Direção Pedagógica')
                ->collapsible()

                ->description('Situação escalada para análise superior.')
                ->schema([

                    Placeholder::make('solicitante')
                        ->label('Pedido feito por:')
                        ->content(fn($record) => $record->requester->name ?? '—'),


                    Placeholder::make('justification_at')
                        ->label('Data da Resposta')
                        ->content(fn($record) => $record->justification_at
                            ? Carbon::parse($record->justification_at)->format('d/m/Y H:i')
                            : '—'),

                    Placeholder::make('justification_escalada')
                        ->label('Justificação para Escalada')
                        ->content(fn($record) => $record->scaled_justification ?? '—')
                        ->visible(fn($record) => $record->status === 'Escalado')
                        ->columnSpanFull(),

                    Placeholder::make('response_coord')
                        ->label('Justificação para Escalada')
                        ->content(fn($record) => $record->response_coord ?? '—')
                        ->visible(fn($record) => $record->status === 'Aprovado DP' || $record->status === 'Recusado DP')
                        ->columnSpanFull(),
                ])
                ->columns(2),


        ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('scheduleConflict.teacher.name')
                    ->label('Professor com Marcações')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('requester.name')
                    ->label('Solicitante')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('scheduleConflict.room.name')
                    ->label('Sala')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('scheduleConflict.timePeriod.description')
                    ->label('Hora')
                    ->sortable(),

                TextColumn::make('scheduleConflict.weekday.weekday')
                    ->label('Dia da Semana')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pendente' => 'warning',
                        'Aprovado' => 'success',
                        'Recusado' => 'danger',
                        'Escalado' => 'info',
                        'Aprovado DP' => 'success',
                        'Recusado DP' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->multiple()
                    ->options([
                        'Pendente' => 'Pendente',
                        'Aprovado' => 'Aprovado',
                        'Recusado' => 'Recusado',
                        'Escalado' => 'Escalado',
                        'Aprovado DP' => 'Aprovado DP',
                        'Recusado DP' => 'Recusado DP',
                    ])
            ])
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
}
