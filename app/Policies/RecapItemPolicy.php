<?php

namespace App\Policies;

use App\Models\RecapItem;
use App\Models\RecapPeriod;
use App\Models\User;

class RecapItemPolicy
{
    /**
     * Pedagang hanya dapat menambah baris rekap saat menjadi mitra transaksi dan periode berstatus 'active'.
     */
    public function store(User $user, RecapPeriod $recapPeriod): bool
    {
        return (int) $user->id === (int) $recapPeriod->trader_id
            && $recapPeriod->status === 'active';
    }

    /**
     * Pedagang hanya dapat mengubah baris rekap miliknya sendiri pada periode berstatus 'active'.
     */
    public function update(User $user, RecapItem $recapItem, ?RecapPeriod $recapPeriod = null): bool
    {
        $period = $recapPeriod ?? $recapItem->recapPeriod;

        return $period !== null
            && (int) $recapItem->recap_period_id === (int) $period->id
            && (int) $user->id === (int) $period->trader_id
            && $period->status === 'active';
    }

    /**
     * Pedagang hanya dapat menghapus baris rekap miliknya sendiri pada periode berstatus 'active'.
     */
    public function destroy(User $user, RecapItem $recapItem, ?RecapPeriod $recapPeriod = null): bool
    {
        return $this->update($user, $recapItem, $recapPeriod);
    }
}
