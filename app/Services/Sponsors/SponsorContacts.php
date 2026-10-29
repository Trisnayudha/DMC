<?php

namespace App\Services\Sponsors;

use App\Models\CmsUser;
use Illuminate\Support\Collection;

/**
 * Helper kecil untuk email notifikasi sponsor (finance / secretariat).
 */
class SponsorContacts
{
    /**
     * Buang kontak (PIC / billing) dobel — email sama dianggap orang yang sama
     * (case-insensitive); kontak tanpa email dibandingkan dari namanya.
     */
    public static function unique(Collection $contacts): Collection
    {
        return $contacts->unique(function ($c) {
            return strtolower(trim($c->email ?: $c->name ?: ''));
        })->values();
    }

    /**
     * Nama user CMS yang melakukan aksi: user CMS yang sedang login, fallback ke
     * created_by (id di cms_users) kalau dipanggil di luar request CMS.
     */
    public static function cmsUserName($cmsUserId = null): string
    {
        $user = auth('cms')->user();

        if (!$user && $cmsUserId) {
            $user = CmsUser::find($cmsUserId);
        }

        return $user->name ?? '-';
    }
}
