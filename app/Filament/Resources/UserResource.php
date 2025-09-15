<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Helpers\ValidationRules;
use App\Models\User;
use Dom\Text;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Impersonate::make(),
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
