<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        // Akun pertama (mis. dari `make:filament-user`) otomatis owner,
        // supaya instalasi baru tidak terkunci tanpa pengelola.
        static::creating(function (self $user): void {
            if ($user->role === null && ! self::query()->exists()) {
                $user->role = Role::Owner;
            }
        });
    }

    /** Semua akun di tabel users adalah staf internal; hak akses per modul lewat role. */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role instanceof Role;
    }

    public function hasRole(Role ...$roles): bool
    {
        return $this->role instanceof Role && in_array($this->role, $roles, true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }
}
