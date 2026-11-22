<?php

namespace App\Policies;

use App\Models\Commodity;
use App\Models\CommodityOffer;
use App\Models\User;

class CommodityOfferPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar tawarannya sendiri.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail tawaran.
     * Hanya pedagang yang mengajukan atau petani pemilik komoditas yang boleh melihat.
     */
    public function view(User $user, CommodityOffer $offer): bool
    {
        return (int) $offer->trader_id === (int) $user->id
            || (int) $offer->commodity->farmer_id === (int) $user->id;
    }

    /**
     * Tentukan apakah pengguna dapat mengajukan tawaran pada komoditas ini.
     * Aturan Anti-Self-Offer: Petani dilarang menawar komoditas miliknya sendiri.
     */
    public function create(User $user, Commodity $commodity): bool
    {
        return (int) $commodity->farmer_id !== (int) $user->id
            && $commodity->status === 'open';
    }

    /**
     * Tentukan apakah pedagang dapat membatalkan tawarannya.
     * Hanya pedagang pemilik tawaran dan hanya jika status masih 'pending'.
     */
    public function delete(User $user, CommodityOffer $offer): bool
    {
        return (int) $offer->trader_id === (int) $user->id
            && $offer->status === 'pending';
    }
}
