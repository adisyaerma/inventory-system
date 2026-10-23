<?php

namespace App\Support;

class CurrentUser
{
    /**
     * Email user yang login lewat Cloudflare Access.
     * Cloudflare menambahkan header ini di setiap request yang lolos login.
     * Kalau header tidak ada (mis. dijalankan lokal / artisan / queue),
     * kembali ke "System".
     */
    public static function email(): string
    {
        $email = trim((string) request()->header('Cf-Access-Authenticated-User-Email', ''));

        return $email !== '' ? mb_strtolower($email) : 'System';
    }
}