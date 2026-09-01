<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class MyAccount extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.my-account';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'a-minha-conta';

    public ?array $data = [];

    public function mount(): void
    {
        /** @var User $user */
        $user = Filament::auth()->user();

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => null,
            'password' => null,
            'password_confirmation' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados pessoais')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Alterar palavra-passe')
                    ->description('Deixe estes campos em branco se não quiser alterar a palavra-passe.')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Palavra-passe atual')
                            ->password()
                            ->revealable()
                            ->autocomplete('current-password'),
                        TextInput::make('password')
                            ->label('Nova palavra-passe')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password'),
                        TextInput::make('password_confirmation')
                            ->label('Confirmar palavra-passe')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password'),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        /** @var User $user */
        $user = Filament::auth()->user();
        $data = $this->form->getState();

        validator($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user),
            ],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O endereço de email é obrigatório.',
            'email.email' => 'Introduza um endereço de email válido.',
            'email.unique' => 'Este endereço de email já está a ser utilizado.',
        ])->validate();

        if (filled($data['password'] ?? null) || filled($data['current_password'] ?? null)) {
            validator($data, [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', PasswordRule::defaults()],
            ], [
                'current_password.required' => 'Introduza a palavra-passe atual.',
                'current_password.current_password' => 'A palavra-passe atual está incorreta.',
                'password.required' => 'Introduza a nova palavra-passe.',
                'password.confirmed' => 'A confirmação da palavra-passe não coincide.',
            ])->validate();
        }

        $emailChanged = $user->email !== $data['email'];

        $user->fill($data);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $this->data['current_password'] = null;
        $this->data['password'] = null;
        $this->data['password_confirmation'] = null;

        Notification::make()
            ->title('Dados da conta atualizados')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'A Minha Conta';
    }
}
