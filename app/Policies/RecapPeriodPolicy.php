<?php

namespace App\Policies;

use App\Models\RecapPeriod;
use App\Models\User;

class RecapPeriodPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar ruang rekap.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail ruang rekap.
     * Hanya petani terkait (farmer_id) atau pedagang terkait (trader_id) yang boleh mengakses.
     */
    public function view(User $user, RecapPeriod $recapPeriod): bool
    {
        return (int) $user->id === (int) $recapPeriod->farmer_id
            || (int) $user->id === (int) $recapPeriod->trader_id;
    }

    /**
     * Tentukan apakah pedagang dapat memulai periode rekap (ready -> active).
     * Hanya pedagang terkait (trader_id) dan hanya jika status masih 'ready'.
     */
    public function start(User $user, RecapPeriod $recapPeriod): bool
    {
        return (int) $user->id === (int) $recapPeriod->trader_id
            && $recapPeriod->status === 'ready';
    }

    /**
     * Tentukan apakah pedagang dapat mengakhiri periode rekap (active -> closed).
     * Hanya pedagang terkait (trader_id) dan hanya jika status masih 'active'.
     */
    public function close(User $user, RecapPeriod $recapPeriod): bool
    {
        return (int) $user->id === (int) $recapPeriod->trader_id
            && $recapPeriod->status === 'active';
    }
}
