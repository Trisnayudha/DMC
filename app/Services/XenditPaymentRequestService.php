<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Migrasi dari xendit/xendit-php SDK lama (VirtualAccounts::create →
 * POST /callback_virtual_accounts) ke Payments API v3
 * (POST /v3/payment_requests). Input & return createVirtualAccount()
 * sengaja memakai key format lama (external_id, bank_code, expected_amount,
 * account_number, dst) supaya pemanggil, tabel payment_users_va, dan
 * response ke FE/mobile tidak perlu berubah.
 *
 * Webhook pembayaran VA v3 masuk ke XenditCallbackController::paymentCallback()
 * (event payment.capture), bukan lagi ke fva_paid().
 */
class XenditPaymentRequestService
{
    const API_VERSION = '2024-11-11';

    /**
     * Kode bank lama DMC (yang tersimpan di payment.payment_method) →
     * channel_code v3. Kebalikannya ada di XenditCallbackController::reverseChannelMap().
     */
    const VA_CHANNEL_MAP = [
        'BCA'     => 'BCA_VIRTUAL_ACCOUNT',
        'BNI'     => 'BNI_VIRTUAL_ACCOUNT',
        'BRI'     => 'BRI_VIRTUAL_ACCOUNT',
        'MANDIRI' => 'MANDIRI_VIRTUAL_ACCOUNT',
        'PERMATA' => 'PERMATA_VIRTUAL_ACCOUNT',
    ];

    /**
     * Buat closed, single-use Virtual Account via Payments API v3.
     *
     * @param array $params external_id, bank_code, name, expected_amount, expiration_date
     * @return array
     * @throws Exception
     */
    public static function createVirtualAccount(array $params): array
    {
        $secretKey = XenditPaymentSessionService::getSecretKey();

        if (empty($secretKey)) {
            Log::error('Xendit secret key is missing in environment variables.');
            throw new Exception('Xendit API key is not configured.');
        }

        $referenceId = (string) ($params['external_id'] ?? '');
        $bankCode    = strtoupper((string) ($params['bank_code'] ?? ''));
        $amount      = (int) round($params['expected_amount'] ?? 0);
        $name        = self::sanitizeDisplayName($params['name'] ?? 'Customer');

        if (!isset(self::VA_CHANNEL_MAP[$bankCode])) {
            throw new Exception("Unsupported virtual account bank: {$bankCode}");
        }

        $channelProperties = ['display_name' => $name];

        $expiresAt = $params['expiration_date'] ?? null;
        if (!empty($expiresAt)) {
            $ts = is_numeric($expiresAt) ? (int) $expiresAt : strtotime($expiresAt);
            $channelProperties['expires_at'] = $ts ? gmdate('Y-m-d\TH:i:s\Z', $ts) : $expiresAt;
        }

        $payload = [
            'reference_id'       => $referenceId,
            'type'               => 'PAY',
            'country'            => 'ID',
            'currency'           => 'IDR',
            'request_amount'     => $amount,
            'channel_code'       => self::VA_CHANNEL_MAP[$bankCode],
            'channel_properties' => $channelProperties,
            'description'        => $params['description'] ?? 'Payment for Djakarta Mining Club',
        ];

        Log::info('Creating Xendit v3 VA payment request', [
            'reference_id' => $referenceId,
            'channel_code' => $payload['channel_code'],
            'amount'       => $amount,
        ]);

        $response = Http::withBasicAuth($secretKey, '')
            ->timeout(15)
            ->withHeaders([
                'api-version'  => self::API_VERSION,
                'accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.xendit.co/v3/payment_requests', $payload);

        if (!$response->successful()) {
            Log::error('Failed to create Xendit v3 VA payment request', [
                'reference_id' => $referenceId,
                'status'       => $response->status(),
                'body'         => $response->body(),
            ]);
            throw new Exception("Xendit API error ({$response->status()}): {$response->body()}");
        }

        $data = $response->json();
        $accountNumber = self::findAction($data, 'VIRTUAL_ACCOUNT_NUMBER');

        if (empty($accountNumber)) {
            Log::error('Xendit v3 VA response has no VIRTUAL_ACCOUNT_NUMBER action', ['response' => $data]);
            throw new Exception('Xendit did not return a virtual account number.');
        }

        Log::info('Xendit v3 VA payment request created', [
            'reference_id'       => $referenceId,
            'payment_request_id' => $data['payment_request_id'] ?? null,
        ]);

        return [
            'id'                 => $data['payment_request_id'] ?? null,
            'payment_request_id' => $data['payment_request_id'] ?? null,
            'external_id'        => $data['reference_id'] ?? $referenceId,
            'owner_id'           => $data['business_id'] ?? null,
            'bank_code'          => $bankCode,
            'merchant_code'      => null,
            'account_number'     => $accountNumber,
            'name'               => $name,
            'currency'           => $data['currency'] ?? 'IDR',
            'country'            => $data['country'] ?? 'ID',
            'expected_amount'    => $data['request_amount'] ?? $amount,
            'is_closed'          => true,
            'is_single_use'      => true,
            'expiration_date'    => $data['channel_properties']['expires_at'] ?? ($channelProperties['expires_at'] ?? null),
            // Status FVA lama saat baru dibuat = PENDING; app/FE masih membaca nilai itu.
            'status'             => 'PENDING',
            'raw'                => $data,
        ];
    }

    /**
     * Ambil value dari actions[] dengan type PRESENT_TO_CUSTOMER & descriptor tertentu.
     */
    private static function findAction(array $data, string $descriptor)
    {
        foreach ($data['actions'] ?? [] as $action) {
            if (($action['descriptor'] ?? null) === $descriptor) {
                return $action['value'] ?? null;
            }
        }

        return null;
    }

    /**
     * Bank menolak karakter non-alfanumerik di nama VA — apostrof dibuang,
     * karakter lain (-, ., /, dst) jadi spasi, supaya request tidak gagal karena nama user.
     */
    private static function sanitizeDisplayName($name): string
    {
        $clean = str_replace(["'", '`'], '', (string) $name);
        $clean = trim(preg_replace('/\s+/', ' ', preg_replace('/[^A-Za-z0-9]/', ' ', $clean)));

        return $clean !== '' ? substr($clean, 0, 50) : 'Customer';
    }
}
