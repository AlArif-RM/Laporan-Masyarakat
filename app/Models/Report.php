<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use App\Support\RegionAdmin;

class Report extends Model
{
    use HasFactory;

    public const STATUSES = ['BARU', 'DIPROSES', 'SELESAI', 'DITOLAK'];

    protected $fillable = [
        'ticket_code',
        'reporter_name',
        'phone',
        'category_id',
        'other_category',
        'title',
        'description',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
        'location_text',
        'rt',
        'rw',
        'status',
    ];

    protected $appends = [
        'display_category',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'province_code', 'code');
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'regency_code', 'code');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'district_code', 'code');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'village_code', 'code');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class)->orderBy('id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(StatusLog::class)
            ->orderByDesc('changed_at')
            ->orderByDesc('id');
    }

    public function getDisplayCategoryAttribute(): string
    {
        $categoryName = $this->category?->name ?? '-';

        if (mb_strtolower(trim($categoryName)) === 'lainnya' && filled($this->other_category)) {
            return $this->other_category;
        }

        return $categoryName;
    }

    public function getLocationDetailAttribute(): string
    {
        return collect([
            $this->village?->name ? 'Kel/Desa '.$this->village->name : null,
            $this->district?->name ? 'Kec. '.$this->district->name : null,
            $this->regency?->name,
            $this->province?->name,
            $this->rt ? 'RT '.$this->rt : null,
            $this->rw ? 'RW '.$this->rw : null,
        ])->filter()->implode(' • ');
    }

    public function scopeVisibleToAdmin(Builder $query, ?User $user): Builder
    {
        if (! $user || $user->isSuperAdmin()) {
            return $query;
        }

        $column = RegionAdmin::reportColumnForRole($user->role);

        if ($column !== null && $user->region_code !== null) {
            return $query->where($column, $user->region_code);
        }

        return $query;
    }

    public static function nextTicketCode(): string
    {
        $year = now()->year;
        $prefix = sprintf('LM-%s-', $year);

        $latestTicket = static::query()
            ->where('ticket_code', 'like', $prefix.'%')
            ->orderByDesc('ticket_code')
            ->value('ticket_code');

        $nextNumber = $latestTicket
            ? ((int) preg_replace('/^LM-\d{4}-/', '', $latestTicket)) + 1
            : 1;

        return $prefix.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public static function metaFor(string $status): array
    {
        return match ($status) {
            'BARU' => ['label' => 'Laporan diterima', 'tone' => 'blue'],
            'DIPROSES' => ['label' => 'Sedang ditindaklanjuti', 'tone' => 'amber'],
            'SELESAI' => ['label' => 'Selesai', 'tone' => 'emerald'],
            'DITOLAK' => ['label' => 'Laporan ditolak', 'tone' => 'red'],
            default => ['label' => $status, 'tone' => 'slate'],
        };
    }

    public function scopeApplyAdminFilters(Builder $query, array $filters): Builder
    {
        $status = $filters['status'] ?? null;
        $search = trim((string) ($filters['q'] ?? ''));
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        if (filled($status) && in_array($status, self::STATUSES, true)) {
            $query->where('status', $status);
        }

        if (filled($dateFrom)) {
            $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }

        if (filled($dateTo)) {
            $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $like = '%'.$search.'%';

                $builder
                    ->where('ticket_code', 'like', $like)
                    ->orWhere('title', 'like', $like)
                    ->orWhere('location_text', 'like', $like)
                    ->orWhere('reporter_name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('other_category', 'like', $like)
                    ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', $like))
                    ->orWhereHas('province', fn (Builder $provinceQuery) => $provinceQuery->where('name', 'like', $like))
                    ->orWhereHas('regency', fn (Builder $regencyQuery) => $regencyQuery->where('name', 'like', $like))
                    ->orWhereHas('district', fn (Builder $districtQuery) => $districtQuery->where('name', 'like', $like))
                    ->orWhereHas('village', fn (Builder $villageQuery) => $villageQuery->where('name', 'like', $like));
            });
        }

        return $query;
    }
}
