<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecapPeriod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'commodity_offer_id',
        'commodity_id',
        'farmer_id',
        'trader_id',
        'locked_capacity',
        'status',
        'started_at',
        'ended_at',
        'paid_at',
        'total_amount',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'locked_capacity' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Tawaran komoditas acuan pembentukan periode rekap ini.
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(CommodityOffer::class, 'commodity_offer_id');
    }

    /**
     * Alias untuk tawaran komoditas acuan (commodityOffer).
     */
    public function commodityOffer(): BelongsTo
    {
        return $this->belongsTo(CommodityOffer::class, 'commodity_offer_id');
    }

    /**
     * Komoditas terkait.
     */
    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class, 'commodity_id');
    }

    /**
     * Petani pemilik komoditas.
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    /**
     * Pedagang mitra transaksi rekap ini.
     */
    public function trader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trader_id');
    }

    /**
     * Seluruh catatan/baris rekap harian dalam periode ini.
     */
    public function recapItems(): HasMany
    {
        return $this->hasMany(RecapItem::class, 'recap_period_id');
    }
}
