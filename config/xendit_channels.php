<?php

/**
 * Daftar channel pembayaran yang ditampilkan di /api/list-payment.
 *
 * Pengganti PaymentChannels::list() (endpoint legacy GET /payment_channels)
 * — Payments API v3 tidak punya endpoint list channel, jadi daftarnya
 * dikelola di sini. Isi HANYA channel yang aktif di akun Xendit produksi.
 *
 * Shape tiap item sengaja sama dengan response lama supaya FE & mobile tidak
 * perlu berubah. `name` dipakai FE untuk mencocokkan ikon, jadi jangan diubah.
 * channel_code VA = kode bank lama DMC (lihat XenditPaymentRequestService::VA_CHANNEL_MAP).
 */

return [
    'channels' => [
        ['channel_code' => 'MANDIRI',    'name' => 'Mandiri virtual account',                'currency' => 'IDR', 'channel_category' => 'VIRTUAL_ACCOUNT'],
        ['channel_code' => 'VISA',       'name' => 'Visa credit and debit cards',            'currency' => 'IDR', 'channel_category' => 'CREDIT_CARD'],
        ['channel_code' => 'MASTERCARD', 'name' => 'Mastercard credit and debit cards',      'currency' => 'IDR', 'channel_category' => 'CREDIT_CARD'],
        ['channel_code' => 'JCB',        'name' => 'JCB credit and debit cards',             'currency' => 'IDR', 'channel_category' => 'CREDIT_CARD'],
        ['channel_code' => 'AMEX',       'name' => 'American Express credit and debit cards', 'currency' => 'IDR', 'channel_category' => 'CREDIT_CARD'],
    ],
];
