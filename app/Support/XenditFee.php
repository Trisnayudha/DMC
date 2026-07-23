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
     * @return array{fee: float, vat: float, total_fee: float, pph23: float, net: float}
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
        $totalFee = $fee + $vat; // potongan otomatis dari settlement Xendit (fee + PPN)

        // PPh 23: withholding 2% dari amount (net_amount = gross - discount),
        // disetor sendiri oleh DMC ke kantor pajak — bukan dipotong Xendit,
        // tapi tetap mengurangi nilai yang DMC akhirnya "kantongi" dari transaksi ini.
        $pph23 = floor((float) config('xendit_fee.pph23_on_amount', 0) * $amount);

        return [
            'fee'       => $fee,
            'vat'       => $vat,
            'total_fee' => $totalFee,
            'pph23'     => $pph23,
            'net'       => max($amount - $totalFee - $pph23, 0),
        ];
    }
}
