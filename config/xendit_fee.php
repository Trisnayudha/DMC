<?php

return [
    // fallback jika payment_method ga match
    'default' => [
        'type' => 'percent_plus_fixed',
        'percent' => 0.00,
        'fixed' => 0,
    ],

    // contoh mapping (silakan kamu edit sesuai fee Xendit kamu)
    'methods' => [
        'Credit Card' => ['type' => 'percent_plus_fixed', 'percent' => 0.029, 'fixed' => 0],
        'VA BCA'      => ['type' => 'fixed', 'fixed' => 4000],
        'VA Mandiri'  => ['type' => 'fixed', 'fixed' => 4000],
        'VA BNI'      => ['type' => 'fixed', 'fixed' => 4000],
        'QRIS'        => ['type' => 'percent_plus_fixed', 'percent' => 0.007, 'fixed' => 0],
        'OVO'         => ['type' => 'percent_plus_fixed', 'percent' => 0.015, 'fixed' => 0],
    ],

    // kalau kamu mau tambah VAT fee di atas fee (opsional)
    'vat_on_fee' => 0.11, // 11% (ubah jika perlu), atau set 0 kalau tidak dipakai
];
