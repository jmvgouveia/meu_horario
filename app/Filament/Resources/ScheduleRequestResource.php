<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleRequestResource\Pages;
use App\Models\ScheduleRequest;
use App\Models\SchoolYear;
use App\Models\Teacher;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
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

    protected int|string|array $pollingInterval = '5s';


    public static function getLabel(): string
    {
        return 'Pedido de Troca de Horário';
    }

    public static function getPluralLabel(): string
    {
        return 'Pedidos de Troca de Horário';
    }

    public static function form(Form $form): Form
    {

        return $form->schema([
            Section::make('Pedido de Troca de Horário')
                ->description('Preencha os campos abaixo para visualizar os detalhes do pedido.')
                ->schema([

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

                    Section::make('Justificação do Pedido')
                        ->description('Motivo indicado pelo docente para solicitar a troca de horário.')
                        ->schema([
                            Placeholder::make('justification')
                                ->label('')
                                ->content(fn($record) => $record->justification ?? '—')
                                ->columnSpanFull()
                        ])
                        ->columns(1),

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
        $user = Filament::auth()->user();
        $isGestor = $user instanceof User && $user->hasRole('Gestor Conflitos');

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
                [],
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
}
