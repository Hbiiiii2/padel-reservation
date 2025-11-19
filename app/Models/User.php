<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'is_admin',
        'membership_code',
        'membership_level',
        'membership_starts_at',
        'membership_expires_at',
        'membership_notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'membership_starts_at' => 'date',
        'membership_expires_at' => 'date',
        'is_admin' => 'boolean',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Hanya allow access ke admin panel untuk user dengan email tertentu
        // Bisa diganti dengan role-based system
        return str_ends_with($this->email, '@padel.com') || $this->is_admin;
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (!$user->is_admin && empty($user->membership_code)) {
                $user->membership_code = self::generateMembershipCode();
            }
        });
    }

    public static function generateMembershipCode(): string
    {
        do {
            $code = 'MBR' . strtoupper(Str::random(5));
        } while (self::where('membership_code', $code)->exists());

        return $code;
    }

    public function getUpcomingReservations()
    {
        return $this->reservations()
            ->upcoming()
            ->with('padelCourt')
            ->orderBy('start_time')
            ->get();
    }

    public function getTodayReservations()
    {
        return $this->reservations()
            ->today()
            ->with('padelCourt')
            ->orderBy('start_time')
            ->get();
    }
}
