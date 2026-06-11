<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Support\RegionAdmin;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';

    public const ROLE_ADMIN_PROVINSI = 'ADMIN_PROVINSI';

    public const ROLE_ADMIN_KABUPATEN_KOTA = 'ADMIN_KABUPATEN_KOTA';

    public const ROLE_ADMIN_KECAMATAN = 'ADMIN_KECAMATAN';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'name',
        'role',
        'region_code',
    ];

    protected $hidden = [
        'password',
    ];

    public function isAdmin(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN_PROVINSI,
            self::ROLE_ADMIN_KABUPATEN_KOTA,
            self::ROLE_ADMIN_KECAMATAN,
        ], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isProvinceAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN_PROVINSI;
    }

    public function isRegencyAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN_KABUPATEN_KOTA;
    }

    public function isDistrictAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN_KECAMATAN;
    }

    public function canAccessReport(Report $report): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $column = RegionAdmin::reportColumnForRole($this->role);

        return $column !== null && $this->region_code !== null && $report->{$column} === $this->region_code;
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_code', 'code');
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
        ];
    }
}
