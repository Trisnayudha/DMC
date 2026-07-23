<?php

/**
 * Estimasi potongan (fee) Xendit per payment method.
 *
 * Model biaya Xendit: fee dasar + PPN 11% di atas fee.
 * Contoh (dari settlement Xendit):
 *   - VA (MANDIRI/BNI/BRI/BCA): Rp 4.000  + PPN 440   → potong 4.440
 *   - QR_CODE / QRIS          : 0,63%     + PPN 11%   (0,63% x 1,11 ≈ 0,7%)
 *   - CREDIT_CARD             : 2,9% + Rp 2.000 + PPN 11%
 *
 * Key HARUS sama persis dengan nilai kolom payment.payment_method.
 * Kalau method tidak terdaftar di sini → pakai 'default' (fee 0),
 * mis. "Approve Manual", "Free-pass Apps", "Invoice", "OTHER".
 */

return [
    // fallback jika payment_method tidak match (fee 0)
    'default' => [
        'type' => 'percent_plus_fixed',
        'percent' => 0.00,
        'fixed' => 0,
    ],

    'methods' => [
        // Kartu kredit: 2,9% + Rp 2.000
        'CREDIT_CARD'   => ['type' => 'percent_plus_fixed', 'percent' => 0.029, 'fixed' => 2000],

        // Virtual Account per bank: flat Rp 4.000
        'MANDIRI'       => ['type' => 'fixed', 'fixed' => 4000],
        'BNI'           => ['type' => 'fixed', 'fixed' => 4000],
        'BRI'           => ['type' => 'fixed', 'fixed' => 4000],
        'BCA'           => ['type' => 'fixed', 'fixed' => 4000],
        'PERMATA'       => ['type' => 'fixed', 'fixed' => 4000],
        'BANK_TRANSFER' => ['type' => 'fixed', 'fixed' => 4000],

        // QRIS: 0,63% (base), PPN otomatis ditambah di bawah
        'QR_CODE'       => ['type' => 'percent', 'percent' => 0.0063],
        'QRIS'          => ['type' => 'percent', 'percent' => 0.0063],

        // E-wallet (perkiraan, sesuaikan bila perlu)
        'OVO'           => ['type' => 'percent', 'percent' => 0.015],
    ],

    // PPN 11% di atas fee
    'vat_on_fee' => 0.11,

    // PPh 23 2% dari amount (net_amount = gross - discount) — withholding tax
    // yang wajib dipotong & disetor sendiri oleh DMC (bukti potong), bukan
    // potongan otomatis dari settlement Xendit.
    'pph23_on_amount' => 0.02,
];
