<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'reservation_code',
        'user_id',
        'padel_court_id',
        'start_time',
        'end_time',
        'duration_hours',
        'price_per_hour',
        'total_price',
        'status',
        'notes',
        'player_names',
        'number_of_players',
        'equipment_rental',
        'equipment_fee',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price_per_hour' => 'decimal:2',
        'total_price' => 'decimal:2',
        'duration_hours' => 'integer',
        'number_of_players' => 'integer',
        'equipment_rental' => 'boolean',
        'equipment_fee' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            if (empty($reservation->reservation_code)) {
                $reservation->reservation_code = 'PAD' . Str::upper(Str::random(6)) . now()->format('dmY');
            }

            // Auto calculate duration and total price
            if ($reservation->start_time && $reservation->end_time) {
                $reservation->duration_hours = $reservation->calculateDuration();
                $reservation->total_price = $reservation->calculateTotalPrice();
            }
        });

        static::updating(function ($reservation) {
            if ($reservation->isDirty(['start_time', 'end_time', 'price_per_hour'])) {
                $reservation->duration_hours = $reservation->calculateDuration();
                $reservation->total_price = $reservation->calculateTotalPrice();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function padelCourt(): BelongsTo
    {
        return $this->belongsTo(PadelCourt::class);
    }

    public function calculateDuration(): int
    {
        return $this->start_time->diffInHours($this->end_time);
    }

    public function calculateTotalPrice(): float
    {
        $basePrice = $this->duration_hours * $this->price_per_hour;
        $equipmentFee = $this->equipment_rental ? $this->equipment_fee : 0;
        return $basePrice + $equipmentFee;
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->start_time->format('d M Y, H:i') . ' - ' . $this->end_time->format('H:i');
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'])
            && $this->start_time->gt(now()->addHours(2));
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now())
            ->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }
}
