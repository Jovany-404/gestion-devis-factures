<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'client_id',
        'password',
    ];

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SALES = 'sales';

    public const ROLE_ACCOUNTANT = 'accountant';

    public const ROLE_CLIENT = 'client';

    public function isAdministrator(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_ACCOUNTANT => 'Comptabilité',
            self::ROLE_CLIENT => 'Client',
            default => 'Commercial',
        };
    }

    public function canManageDocuments(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SALES], true);
    }

    public function canAccessStaffWorkspace(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            self::ROLE_SALES,
            self::ROLE_ACCOUNTANT,
        ], true);
    }

    public function canAccessClientPortal(): bool
    {
        return $this->role === self::ROLE_CLIENT && $this->client_id !== null;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'created_by');
    }

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
            'password' => 'hashed',
        ];
    }
}
