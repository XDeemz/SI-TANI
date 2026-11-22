<?php

namespace App\Policies;

use App\Models\Commodity;
use App\Models\User;

class CommodityPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar komoditas miliknya.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail komoditas tertentu.
     */
    public function view(User $user, Commodity $commodity): bool
    {
        return (int) $commodity->farmer_id === (int) $user->id;
    }

    /**
     * Tentukan apakah pengguna dapat membuat komoditas.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui komoditas miliknya.
     */
    public function update(User $user, Commodity $commodity): bool
    {
        return (int) $commodity->farmer_id === (int) $user->id;
    }

    /**
     * Tentukan apakah pengguna dapat menghapus komoditas miliknya.
     */
    public function delete(User $user, Commodity $commodity): bool
    {
        return (int) $commodity->farmer_id === (int) $user->id;
    }

    /**
     * Tentukan apakah pengguna dapat mengubah status ketersediaan komoditas miliknya.
     */
    public function toggleStatus(User $user, Commodity $commodity): bool
    {
        return (int) $commodity->farmer_id === (int) $user->id;
    }
}
