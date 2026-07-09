<?php

namespace App\Support;

/**
 * Estimasi potongan Xendit berdasarkan payment method.
 * Parameter fee diambil dari config/xendit_fee.php.
 *
 * Dipakai bersama oleh FinancialReportController dan FinancialReportExport
 * supaya angka di tabel, Excel, dan PDF selalu sama.
 */
class XenditFee
{
    /**
     * @param  string|null $method  nilai payment.payment_method
     * @param  float|int   $amount  nominal yang dibayar (net = gross - discount)
     * @return array{fee: float, vat: float, total_fee: float, net: float}
     */
    public static function estimate($method, $amount): array
    {
        $amount = (float) $amount;
        $methods = config('xendit_fee.methods', []);
        $cfg = isset($methods[$method]) ? $methods[$method] : config('xendit_fee.default');

        $type = isset($cfg['type']) ? $cfg['type'] : '';
        $percent = (float) (isset($cfg['percent']) ? $cfg['percent'] : 0);
        $fixed   = (float) (isset($cfg['fixed']) ? $cfg['fixed'] : 0);

        if ($type === 'fixed') {
            $fee = $fixed;
        } elseif ($type === 'percent') {
            $fee = $amount * $percent;
        } else { // percent_plus_fixed
            $fee = ($amount * $percent) + $fixed;
        }

        // Xendit membulatkan ke bawah (whole rupiah) untuk fee & PPN.
        $fee = floor($fee);
        $vat = floor((float) config('xendit_fee.vat_on_fee', 0) * $fee);
        $totalFee = $fee + $vat;

        return [
            'fee'       => $fee,
            'vat'       => $vat,
            'total_fee' => $totalFee,
            'net'       => max($amount - $totalFee, 0),
        ];
    }
}
