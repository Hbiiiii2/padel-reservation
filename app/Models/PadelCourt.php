<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class PadelCourt extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'price_per_hour',
        'max_players',
        'is_active',
        'facilities',
        'image',
        'operational_hours',
    ];

    protected $casts = [
        'facilities' => 'array',
        'is_active' => 'boolean',
        'price_per_hour' => 'decimal:2',
        'max_players' => 'integer',
        'operational_hours' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($court) {
            if (empty($court->slug)) {
                $court->slug = Str::slug($court->name);
            }
        });
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price_per_hour, 0, ',', '.');
    }

    public function isAvailable($startTime, $endTime, $excludeReservationId = null): bool
    {
        $query = $this->reservations()
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->whereIn('status', ['pending', 'confirmed']);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return !$query->exists();
    }

    public function getUpcomingReservations()
    {
        return $this->reservations()
            ->where('start_time', '>=', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_time')
            ->get();
    }
}
