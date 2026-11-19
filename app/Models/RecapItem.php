<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecapItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'recap_period_id',
        'transaction_date',
        'net_weight',
        'actual_price',
        'subtotal',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'net_weight' => 'decimal:2',
            'actual_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Periode rekap induk dari item rekap harian ini.
     */
    public function recapPeriod(): BelongsTo
    {
        return $this->belongsTo(RecapPeriod::class, 'recap_period_id');
    }
}
