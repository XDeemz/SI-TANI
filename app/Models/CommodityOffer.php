<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommodityOffer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'commodity_id',
        'trader_id',
        'offered_daily_capacity',
        'offered_price',
        'status',
        'farmer_notes',
        'responded_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'offered_daily_capacity' => 'decimal:2',
            'offered_price' => 'decimal:2',
            'responded_at' => 'datetime',
        ];
    }

    /**
     * Komoditas yang ditawar.
     */
    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class, 'commodity_id');
    }

    /**
     * Pedagang yang mengajukan tawaran.
     */
    public function trader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trader_id');
    }

    /**
     * Periode rekap yang terbentuk ketika tawaran ini disetujui.
     */
    public function recapPeriod(): HasOne
    {
        return $this->hasOne(RecapPeriod::class, 'commodity_offer_id');
    }
}
