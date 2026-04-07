<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'is_active',
        'desa_id',
        'kelompok_id',
        'no_telepon',
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
     * Get attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $appends = ['scope_label'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ... constants and relations ...

    // ============================================
    // Accessors
    // ============================================
    public function getScopeLabelAttribute(): string
    {
        return $this->getScopeLabel();
    }

    // ============================================
    // Role Constants (3-Tier Admin System + Developer)
    // ============================================
    const ROLE_DEVELOPER = 'developer';

    const ROLE_SUPER_ADMIN = 'super_admin';

    const ROLE_ADMIN_DESA = 'admin_desa';

    const ROLE_ADMIN_KELOMPOK = 'admin_kelompok';

    // ============================================
    // Relationships
    // ============================================
    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    public function kelompok()
    {
        return $this->belongsTo(Kelompok::class);
    }

    // ============================================
    // Role Helper Methods
    // ============================================
    public function isDeveloper(): bool
    {
        return $this->role === self::ROLE_DEVELOPER;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN || $this->isDeveloper();
    }

    public function isAdminDesa(): bool
    {
        return $this->role === self::ROLE_ADMIN_DESA;
    }

    public function isAdminKelompok(): bool
    {
        return $this->role === self::ROLE_ADMIN_KELOMPOK;
    }

    /**
     * Check if this user can manage another user
     */
    public function canManageUser(User $user): bool
    {
        if ($this->isDeveloper()) {
            return true;
        }

        if ($this->isSuperAdmin()) {
            if ($user->isDeveloper()) {
                return false;
            }
            if ($user->isSuperAdmin()) {
                return false;
            }

            return true;
        }

        if ($this->isAdminDesa() && $user->isAdminKelompok()) {
            return $user->desa_id === $this->desa_id;
        }

        return false;
    }

    /**
     * Get scope label for display (e.g., "Desa Jati", "Kelompok Jati Lama")
     */
    public function getScopeLabel(): string
    {
        if ($this->isDeveloper()) {
            return 'Developer / System Owner';
        } elseif ($this->isSuperAdmin()) {
            return 'Seluruh Sistem';
        } elseif ($this->isAdminDesa()) {
            return 'Desa '.($this->desa?->nama_desa ?? 'N/A');
        } elseif ($this->isAdminKelompok()) {
            return 'Kelompok '.($this->kelompok?->nama_kelompok ?? 'N/A');
        }

        return 'N/A';
    }

    /**
     * Legacy compatibility - kept for backward compatibility
     *
     * @deprecated Use isSuperAdmin() instead
     */
    public function isAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user can access dashboard (any active admin)
     */
    public function canAccessDashboard(): bool
    {
        return $this->is_active && in_array($this->role, [
            self::ROLE_DEVELOPER,
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN_DESA,
            self::ROLE_ADMIN_KELOMPOK,
        ]);
    }
}
