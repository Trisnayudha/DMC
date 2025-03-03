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

        // 3. Jika voucher code kosong, langsung kembalikan harga normal
        if (empty($code_voucher)) {
            return response()->json([
                'status'  => 200,
                'message' => 'No voucher code provided',
                'payload' => [
                    'original_price' => $price,
                    'discount'       => 0,
                    'final_price'    => $price
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

        // 6. Kembalikan hasil perhitungan dengan casting yang tepat
        return response()->json([
            'status'  => 200,
            'message' => 'success',
            'payload' => [
                'voucher_code'   => $code_voucher,
                'original_price' => (int) $price,
                'discount'       => (int) $discount,
                'final_price'    => (int) $finalPrice
            ]
        ]);
    }
}
