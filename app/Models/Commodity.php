<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commodity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'farmer_id',
        'name',
        'unit',
        'initial_daily_capacity',
        'remaining_daily_capacity',
        'estimated_harvest_date',
        'notes',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'initial_daily_capacity' => 'decimal:2',
            'remaining_daily_capacity' => 'decimal:2',
            'estimated_harvest_date' => 'date',
        ];
    }

    /**
     * Petani pemilik komoditas.
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    /**
     * Tawaran pra-panen yang masuk untuk komoditas ini.
     */
    public function offers(): HasMany
    {
        return $this->hasMany(CommodityOffer::class, 'commodity_id');
    }

    /**
     * Periode rekap yang berjalan untuk komoditas ini.
     */
    public function recapPeriods(): HasMany
    {
        return $this->hasMany(RecapPeriod::class, 'commodity_id');
    }
}
