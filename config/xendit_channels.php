<?php

/**
 * Daftar channel pembayaran yang ditampilkan di /api/list-payment.
 *
 * Pengganti PaymentChannels::list() (endpoint legacy GET /payment_channels)
 * — Payments API v3 tidak punya endpoint list channel, jadi daftarnya
 * dikelola di sini. Shape tiap item sengaja sama dengan response lama
 * (channel_code, name, currency, channel_category, is_enabled) supaya
 * FE & mobile tidak perlu berubah.
 *
 * channel_code VA = kode bank lama DMC (lihat XenditPaymentRequestService::VA_CHANNEL_MAP).
 * Bank bisa dimatikan tanpa deploy lewat env, mis. XENDIT_VA_CHANNELS="BRI,BNI,MANDIRI".
 */

return [
    'virtual_accounts' => array_filter(array_map('trim', explode(',', env('XENDIT_VA_CHANNELS', 'BCA,BRI,BNI,MANDIRI,PERMATA')))),

    'channels' => [
        ['channel_code' => 'BCA',     'name' => 'BCA virtual account',     'currency' => 'IDR', 'channel_category' => 'VIRTUAL_ACCOUNT'],
        ['channel_code' => 'BRI',     'name' => 'BRI virtual account',     'currency' => 'IDR', 'channel_category' => 'VIRTUAL_ACCOUNT'],
        ['channel_code' => 'BNI',     'name' => 'BNI virtual account',     'currency' => 'IDR', 'channel_category' => 'VIRTUAL_ACCOUNT'],
        ['channel_code' => 'MANDIRI', 'name' => 'Mandiri virtual account', 'currency' => 'IDR', 'channel_category' => 'VIRTUAL_ACCOUNT'],
        ['channel_code' => 'PERMATA', 'name' => 'Permata virtual account', 'currency' => 'IDR', 'channel_category' => 'VIRTUAL_ACCOUNT'],
        ['channel_code' => 'VISA',    'name' => 'VISA',                    'currency' => 'IDR', 'channel_category' => 'CREDIT_CARD'],
    ],
];
