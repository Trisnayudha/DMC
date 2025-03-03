<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Events\EventsTicket;
use App\Models\Vouchers\Voucher;
use Illuminate\Http\Request;


class VoucherController extends Controller
{
    public function discount(Request $request)
    {
        $ticket_id    = $request->ticket_id;
        $code_voucher = $request->code_voucher;

        // 1. Cek apakah ticket_id valid
        $ticket = EventsTicket::where('id', $ticket_id)->first();
        if (!$ticket) {
            return response()->json([
                'status'  => 404,
                'message' => 'Ticket not found',
                'payload' => null
            ], 404);
        }

        // 2. Ambil harga normal tiket dan pastikan berupa integer
        $price = (int) $ticket->price_rupiah;

        // Ambil nilai tukar dollar dari helper (1$ = X rupiah)
        $exchangeRate = \App\Helpers\ScrapeHelper::scrapeExchangeRate();

        // 3. Jika voucher code kosong, langsung kembalikan harga normal
        if (empty($code_voucher)) {
            return response()->json([
                'status'  => 200,
                'message' => 'No voucher code provided',
                'payload' => [
                    'voucher_code'          => null,
                    'original_price'        => $price,
                    'discount'              => 0,
                    'final_price'           => $price,
                    'original_price_dollar' => round($price / $exchangeRate, 2),
                    'discount_dollar'       => 0,
                    'final_price_dollar'    => round($price / $exchangeRate, 2),
                    'voucher_dollar'        => $exchangeRate
                ]
            ]);
        }

        // 4. Cek apakah voucher ada dan masih aktif
        $voucher = Voucher::where('voucher_code', $code_voucher)
            ->where('status', 'active')
            ->first();
        if (!$voucher) {
            return response()->json([
                'status'  => 400,
                'message' => 'Voucher not found or inactive',
                'payload' => null
            ], 400);
        }

        // 5. Lakukan perhitungan diskon
        $discount   = 0;
        $finalPrice = $price; // Default

        if ($voucher->type == 'fixed') {
            $discount = (int) $voucher->nominal;
            if ($discount > $price) {
                $discount = $price;
            }
            $finalPrice = $price - $discount;
        } else {
            // Asumsi type = 'percentage'
            $persen   = (int) $voucher->nominal;
            $discount = ($price * $persen) / 100;
            $discount = (int) $discount;
            $finalPrice = $price - $discount;
        }

        // 6. Hitung nilai dollar menggunakan exchange rate (dollar = rupiah / exchangeRate)
        $originalPriceDollar = round($price / $exchangeRate, 2);
        $discountDollar = round($discount / $exchangeRate, 2);
        $finalPriceDollar = round($finalPrice / $exchangeRate, 2);

        // 7. Kembalikan hasil perhitungan dengan casting yang tepat
        return response()->json([
            'status'  => 200,
            'message' => 'success',
            'payload' => [
                'voucher_code'          => $code_voucher,
                'original_price'        => $price,
                'discount'              => $discount,
                'final_price'           => $finalPrice,
                'original_price_dollar' => $originalPriceDollar,
                'discount_dollar'       => $discountDollar,
                'final_price_dollar'    => $finalPriceDollar,
                'voucher_dollar'        => $exchangeRate
            ]
        ]);
    }
}
