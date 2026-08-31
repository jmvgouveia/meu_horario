<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Helpers\ValidationRules;
use App\Models\User;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;




class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $navigationLabel = 'Utilizadores';
    protected static ?string $navigationIcon = 'heroicon-s-user-group';



    public static function getLabel(): string
    {
        return 'Utilizador';
    }

    public static function getPluralLabel(): string
    {
        return 'Utilizadores';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Introduza nome'),
                TextInput::make('email')
                    ->label('E-mail')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Introduza e-mail'),
                TextInput::make('password')
                    ->label('Password')
                    ->placeholder('Introduza password')
                    ->password()
                    ->dehydrated(fn($state) => filled($state))
                    ->nullable()
                    ->minLength(5)
                    ->regex(ValidationRules::PASSWORD_REGEX)
                    ->helperText(ValidationRules::PASSWORD_HELPER_MSG),
                Select::make('roles')->multiple()->relationship('roles', 'name')->preload()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->toggleable()
                    ->width('10%'),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-envelope')
                    ->iconColor('primary'),
                TextColumn::make('mfa_status')
                    ->label('MFA')
                    ->state(fn (User $record): string => $record->hasTwoFactorEnabled() ? 'Ativa' : 'Pendente')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Ativa' ? 'success' : 'warning'),
                TextColumn::make('mfa_grace_until')
                    ->label('Prazo MFA')
                    ->date('d/m/Y')
                    ->placeholder('Expirado'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Impersonate::make()
                    ->label('Ver como utilizador')
                    ->redirectTo('/maestro'),
                Action::make('renewMfaGrace')
                    ->label('Renovar prazo MFA')
                    ->icon('heroicon-o-clock')
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        abort_unless(auth()->user()?->isSuperAdmin(), 403);

                        $record->forceFill([
                            'mfa_grace_until' => now()->addDays((int) config('two-factor.grace_days')),
                            'mfa_grace_renewed_at' => now(),
                            'mfa_grace_renewed_by' => auth()->id(),
                        ])->save();
                    }),
                Action::make('resetMfa')
                    ->label('Repor MFA')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->visible(fn (User $record): bool => (auth()->user()?->isSuperAdmin() ?? false) && ! $record->is(auth()->user()))
                    ->requiresConfirmation()
                    ->modalHeading('Repor autenticação multifator')
                    ->modalDescription('A autenticação multifator será desativada e todas as sessões deste utilizador serão terminadas.')
                    ->action(function (User $record): void {
                        abort_unless(auth()->user()?->isSuperAdmin(), 403);
                        abort_if($record->is(auth()->user()), 422, 'Não pode repor o MFA da sua própria conta.');

                        if ($record->twoFactorAuth()->exists()) {
                            $record->disableTwoFactorAuth();
                        }

                        $record->forceFill([
                            'remember_token' => Str::random(60),
                            'mfa_grace_until' => now(),
                            'mfa_grace_renewed_at' => now(),
                            'mfa_grace_renewed_by' => auth()->id(),
                        ])->save();

                        DB::table('sessions')->where('user_id', $record->getKey())->delete();
                    }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
