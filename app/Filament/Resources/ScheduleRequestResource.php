<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleRequestResource\Pages;
use App\Models\Schedule;
use App\Models\ScheduleRequest;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class ScheduleRequestResource extends Resource
{
    protected static ?string $model = ScheduleRequest::class;

    protected static ?string $navigationGroup = 'Calendarização';
    protected static ?string $navigationLabel = 'Pedidos de Troca';
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';
    protected static ?string $navigationBadgeTooltip = 'Pedidos recebidos';
    protected static ?int $navigationSort = 2;

    public static function getLabel(): string
    {
        return 'Pedido de Troca de Horário';
    }

    public static function getPluralLabel(): string
    {
        return 'Pedidos de Troca de Horário';
    }

    public static function getEloquentQuery(): Builder
    {
        $userId = Filament::auth()->id();
        $user = Filament::auth()->user();

        // --- VALIDAR COM PERMISSÕES ---
        // ✅ Se for um gestor (por ID ou por papel), vê tudo
        // if (in_array($user?->id, [1])) { //|| $user?->hasRole('admin')) {
        //     return parent::getEloquentQuery();
        // }

        $teacher = \App\Models\Teacher::where('id_user', $userId)->first();

        return parent::getEloquentQuery()
            ->where(function ($query) use ($teacher) {
                $query
                    ->where('id_teacher', $teacher?->id)
                    ->orWhereHas('scheduleConflict', function ($subQuery) use ($teacher) {
                        $subQuery->where('id_teacher', $teacher?->id);
                    });
            });
    }


    public static function form(Form $form): Form
    {
        // return $form
        // ->schema([
        //     Section::make('Pedido de Troca de Horário')
        //         ->description('Preencha os campos abaixo para solicitar uma troca de horário.')
        //         ->columns(3)
        //         ->schema([

        //             Section::make('Justificação do Pedido')
        //                 ->description('Motivo indicado pelo docente para solicitar a troca de horário.')
        //                 ->schema([
        //                     Placeholder::make('solicitante')
        //                         ->label('Pedido feito por:')
        //                         ->content(fn($record) => $record->requester->name ?? '—'),

        //                     Placeholder::make('data_pedido')
        //                         ->label('Data do Pedido')
        //                         ->content(fn($record) => optional($record->created_at)->format('d/m/Y H:i') ?? '—'),
        //                     //->columnSpanFull(),
        //                     Placeholder::make('status')
        //                         ->label('Estado Atual')
        //                         ->content(fn($record) => $record->status ?? '—'),

        //                     Placeholder::make('justification')
        //                         ->label('Justificação do Pedido')
        //                         ->content(fn($record) => $record->justification ?? '—')
        //                         ->columnSpanFull(),
        //                 ])
        //                 ->columns(3)
        //                 ->extraAttributes([
        //                     'class' => 'bg-white border border-gray-300 rounded-xl shadow-sm',
        //                 ]),


        //             Grid::make(2)
        //                 ->schema([]),

        //             Section::make('Resposta do Professor Original')
        //                 ->description('Resposta ao pedido.')
        //                 ->schema([
        //                     Grid::make(2)
        //                         ->schema([
        //                             Placeholder::make('professor_respondeu')
        //                                 ->label('Resposta de:')
        //                                 ->content(fn($record) => $record->scheduleConflict->teacher->name ?? '—'),

        //                             Placeholder::make('data_pedido')
        //                                 ->label('Data da Resposta')
        //                                 ->content(fn($record) => optional($record->responded_at)->format('d/m/Y H:i') ?? '—'),

        //                         ])
        //                         ->columns(2),

        //                     Placeholder::make('response')
        //                         ->label('Motivo da Recusa')
        //                         ->content(fn($record) => $record->response ?? '—')
        //                         ->disabled()
        //                         ->columnSpanFull(),
        //                 ])
        //                 ->extraAttributes([
        //                     'class' => 'bg-white border border-gray-300 rounded-xl shadow-sm',
        //                 ]),









        //             Textarea::make('response_coord')
        //                 ->label('Notas de Aprovação')
        //                 ->visible(fn($get) => $get('status') === 'Aprovado')
        //                 ->disabled()
        //                 ->columnSpanFull(),
        //             Textarea::make('scaled_justification')
        //                 ->label('Notas de Aprovação')
        //                 ->visible(fn($get) => $get('status') === 'Escalado')
        //                 ->disabled()
        //                 ->columnSpanFull(),

        //         ]),

        // ]);

        return $form->schema([
            Section::make('Pedido de Troca de Horário')
                ->description('Preencha os campos abaixo para visualizar os detalhes do pedido.')
                ->schema([

                    // Linha superior com 3 campos
                    Grid::make(3)->schema([
                        Placeholder::make('solicitante')
                            ->label('Pedido feito por')
                            ->content(fn($record) => $record->requester->name ?? '—'),


                        Placeholder::make('data_pedido')
                            ->label('Data do Pedido')
                            ->content(fn($record) => optional($record->created_at)->format('d/m/Y H:i') ?? '—'),

                        Placeholder::make('status')
                            ->label('Estado Atual')
                            ->content(fn($record) => $record->status ?? '—'),

                    ]),

                    // Justificação destacada
                    Section::make('Justificação do Pedido')
                        ->description('Motivo indicado pelo docente para solicitar a troca de horário.')
                        ->schema([
                            Placeholder::make('justification')
                                ->label('')
                                ->content(fn($record) => $record->justification ?? '—')
                                ->columnSpanFull()
                        ])
                        ->columns(1),

                    // Campos condicionais com estilo uniforme
                    Placeholder::make('response')
                        ->label('Motivo da Recusa')
                        ->content(fn($record) => $record->response ?? '—')
                        ->visible(fn($get) => $get('status') === 'Recusado')
                        ->columnSpanFull(),

                    Placeholder::make('responded_at')
                        ->label('Data da Resposta')
                        ->content(fn($record) => optional($record->responded_at)->format('d/m/Y H:i') ?? '—')
                        ->visible(fn($get) => $get('status') === 'Recusado')
                        ->columnSpanFull(),

                    Placeholder::make('response_coord')
                        ->label('Resposta do Docente')
                        ->content(fn($record) => $record->response ?? '—')
                        ->visible(fn($get) => $get('status') === 'Aprovado')
                        ->columnSpanFull(),

                    Placeholder::make('scaled_justification')
                        ->label('Notas da Escalada')
                        ->content(fn($record) => $record->scaled_justification ?? '—')
                        ->visible(fn($get) => $get('status') === 'Escalado')
                        ->columnSpanFull(),

                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $userId = Filament::auth()->id();
        $isGestor = in_array($userId, [1]); // ou usa uma função global/policy

        $columns = [
            TextColumn::make('id')
                ->label('ID do Pedido')
                ->toggleable()
                ->sortable(),
            TextColumn::make('requester.name')
                ->label('Requerente')
                ->wrap()
                ->toggleable()
                ->limit(25),

            TextColumn::make('scheduleConflict.weekday.weekday')
                ->label('Dia da Semana')
                ->wrap()
                ->toggleable()
                ->limit(50),
            TextColumn::make('scheduleConflict.timePeriod.description')
                ->label('Hora da Aula')
                ->wrap()
                ->toggleable()
                ->limit(50),
            TextColumn::make('scheduleConflict.room.name')
                ->label('Sala')
                ->toggleable()
                ->limit(50),
            TextColumn::make('justification')
                ->label('Justificação do Pedido')
                ->wrap()
                ->toggleable()
                ->limit(50),
            TextColumn::make('status')
                ->label('Estado do Pedido')
                ->toggleable()
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
                ->sortable()


        ];

        if ($isGestor) {
            $columns = array_merge($columns, [
                TextColumn::make('scheduleConflict.teacher.name')
                    ->label('Professor Original')
                    ->wrap()
                    ->toggleable()
                    ->limit(50),
                TextColumn::make('response')
                    ->label('Resposta do Professor')
                    ->wrap()
                    ->toggleable()
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label('Data de Criação')
                    ->dateTime()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Data de Atualização')
                    ->dateTime()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('id')
                    ->label('ID do Pedido')
                    ->toggleable()
                    ->sortable(),

            ]);
        }

        return $table
            ->columns($columns)
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Pendente' => 'Pendente',
                        'Aprovado' => 'Aprovado',
                        'Recusado' => 'Recusado',
                        'Escalado' => 'Escalado',
                        'Aprovado DP' => 'Aprovado DP',
                        'Recusado DP' => 'Recusado DP',
                    ])
                    ->multiple()
                    ->label('Estado'),

                ...($isGestor ? [
                    SelectFilter::make('id_teacher')
                        ->relationship('requester', 'name')
                        ->label('Requerente')
                        ->multiple(),


                ] : []),
            ])
            ->actions([])
            ->bulkActions(
                [
                    // Tables\Actions\BulkActionGroup::make([
                    //     Tables\Actions\DeleteBulkAction::make(),
                    // ])
                ],
            );
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
            'index' => Pages\ListScheduleRequests::route('/'),
            'create' => Pages\CreateScheduleRequest::route('/create'),
            'edit' => Pages\EditScheduleRequest::route('/{record}/edit'),
        ];
    }

    // Apresentar o total de pedidos recebidos pendentes
    public static function getNavigationBadge(): ?string
    {
        $user = Filament::auth()->user();

        if (!$user || !$user->teacher) {
            return null;
        }

        $teacherId = $user->teacher->id;

        $conflictingSchedules = Schedule::where('id_teacher', $teacherId)
            ->get(['id_schoolyear', 'id_timeperiod', 'id_room', 'id_weekday']);

        if ($conflictingSchedules->isEmpty()) {
            return null;
        }

        $query = \App\Models\ScheduleRequest::where('status', 'Pendente')
            // Apenas pedidos feitos por OUTROS (não pelo utilizador atual)
            ->where('id_teacher', '!=', $teacherId)
            ->where(function ($query) use ($conflictingSchedules, $teacherId) {
                foreach ($conflictingSchedules as $schedule) {
                    $query->orWhere(function ($sub) use ($schedule, $teacherId) {
                        $sub->whereHas('scheduleConflict', function ($q) use ($schedule, $teacherId) {
                            $q->where('id_teacher', $teacherId) // confirmar que o horário-alvo é do utilizador atual
                                ->where('id_schoolyear', $schedule->id_schoolyear)
                                ->where('id_timeperiod', $schedule->id_timeperiod)
                                ->where('id_room', $schedule->id_room)
                                ->where('id_weekday', $schedule->id_weekday);
                        });
                    });
                }
            });

        return (string) $query->count();
    }

    // public static function getPermissionPrefixes(): array
    // {
    //     return ['schedule_request'];
    // }
}
