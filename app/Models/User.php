<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'payment_info',
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
            'password' => 'hashed',
        ];
    }

    /**
     * Komoditas yang dimiliki pengguna sebagai Petani.
     */
    public function commodities(): HasMany
    {
        return $this->hasMany(Commodity::class, 'farmer_id');
    }

    /**
     * Tawaran yang diajukan pengguna sebagai Pedagang.
     */
    public function offers(): HasMany
    {
        return $this->hasMany(CommodityOffer::class, 'trader_id');
    }

    /**
     * Periode rekap di mana pengguna bertindak sebagai Petani.
     */
    public function farmerRecapPeriods(): HasMany
    {
        return $this->hasMany(RecapPeriod::class, 'farmer_id');
    }

    /**
     * Periode rekap di mana pengguna bertindak sebagai Pedagang.
     */
    public function traderRecapPeriods(): HasMany
    {
        return $this->hasMany(RecapPeriod::class, 'trader_id');
    }
}
