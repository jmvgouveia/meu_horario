<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Althinect\FilamentSpatieRolesPermissions\Concerns\HasSuperAdmin;
use App\Http\Middleware\EnforceMfa;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laragear\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Laragear\TwoFactor\TwoFactorAuthentication;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, TwoFactorAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, HasSuperAdmin, Notifiable, TwoFactorAuthentication;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'activation_token',
        'activation_token_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mfa_grace_renewed_at' => 'datetime',
            'mfa_grace_until' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'activation_token_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'id_user');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function mfaGraceRenewedBy()
    {
        return $this->belongsTo(self::class, 'mfa_grace_renewed_by');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->hasAnyRole([
                'Super Admin',
                'Secretaria',
                'Professor',
                'Gestor Conflitos',
                'Recursos Humanos',
                'Área Pedagógica',
                'Aluno',
            ]);
    }

    public function isGestorConflitos(): bool
    {
        return $this->hasRole('Gestor Conflitos');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('Professor');
    }

    public function canImpersonate(): bool
    {
        return $this->isSuperAdmin()
            && $this->hasTwoFactorEnabled()
            && (int) session(EnforceMfa::SESSION_KEY) === $this->getKey();
    }

    public function canBeImpersonated(): bool
    {
        $panel = Filament::getCurrentPanel() ?? Filament::getPanel('admin');

        return $this->canAccessPanel($panel);
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            $user->mfa_grace_until ??= now()->addDays((int) config('two-factor.grace_days'));
        });
    }

    public function isMfaGraceExpired(): bool
    {
        return $this->mfa_grace_until?->isPast() ?? true;
    }
}
