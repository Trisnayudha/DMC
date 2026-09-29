<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Migrasi dari xendit/xendit-php SDK lama (Invoice::create / VirtualAccounts::create)
 * ke REST API Payment Sessions Xendit (POST /sessions). Return array dari
 * createPaymentSession() sengaja dibentuk supaya key-nya sama persis dengan
 * yang dibaca App\Helpers\XenditInvoice::saveInvoice() — jadi layer
 * penyimpanan invoice existing tidak perlu berubah.
 */
class XenditPaymentSessionService
{
    /**
     * Get Xendit Secret Key based on environment.
     *
     * @return string
     */
    public static function getSecretKey(): string
    {
        $isProd = (bool) env('XENDIT_ISPROD');
        $key = $isProd ? env('XENDIT_SECRET_KEY_PROD') : env('XENDIT_SECRET_KEY_TEST');

        return (string) $key;
    }

    /**
     * Create a modern Payment Session (POST /sessions) with mode PAYMENT_LINK.
     *
     * @param array $params
     * @return array
     * @throws Exception
     */
    public static function createPaymentSession(array $params): array
    {
        $secretKey = self::getSecretKey();

        if (empty($secretKey)) {
            Log::error('Xendit secret key is missing in environment variables.');
            throw new Exception('Xendit API key is not configured.');
        }

        $referenceId = $params['reference_id'] ?? $params['external_id'] ?? '';
        $amount = (int) round($params['amount'] ?? 0);
        $customer = $params['customer'] ?? [];

        // Support passing payer_email directly
        if (empty($customer) && !empty($params['payer_email'])) {
            $customer = [
                'email' => $params['payer_email'],
                'name'  => $params['payer_name'] ?? 'Customer',
            ];
        }

        $successUrl = $params['success_return_url'] ?? $params['success_redirect_url'] ?? 'https://djakarta-miningclub.com/';
        $cancelUrl  = $params['cancel_return_url'] ?? $params['failure_redirect_url'] ?? 'https://djakarta-miningclub.com/';

        $payload = [
            'reference_id'       => (string) $referenceId,
            'session_type'       => 'PAY',
            'mode'               => 'PAYMENT_LINK',
            'amount'             => $amount,
            'currency'           => $params['currency'] ?? 'IDR',
            'country'            => $params['country'] ?? 'ID',
            'description'        => $params['description'] ?? 'Payment for Djakarta Mining Club',
            'success_return_url' => self::formatReturnUrl($successUrl),
            'cancel_return_url'  => self::formatReturnUrl($cancelUrl),
        ];

        // Customer details
        if (!empty($customer)) {
            $individualDetail = [
                'given_names' => !empty($customer['given_names']) ? $customer['given_names'] : (!empty($customer['name']) ? $customer['name'] : 'Customer'),
            ];
            if (!empty($customer['surname'])) {
                $individualDetail['surname'] = $customer['surname'];
            }

            $customerPayload = [
                'type'              => 'INDIVIDUAL',
                'reference_id'      => !empty($customer['reference_id']) ? (string) $customer['reference_id'] : 'cust_' . ($referenceId ?: uniqid()),
                'individual_detail' => $individualDetail,
            ];

            if (!empty($customer['email'])) {
                $customerPayload['email'] = $customer['email'];
            }
            if (!empty($customer['mobile_number'])) {
                $customerPayload['mobile_number'] = $customer['mobile_number'];
            }

            $payload['customer'] = $customerPayload;
        }

        // Allowed payment channels / methods filtering if requested
        if (!empty($params['allowed_payment_channels']) && is_array($params['allowed_payment_channels'])) {
            $channelMap = [
                'CREDIT_CARD' => 'CARDS',
                'CARD'        => 'CARDS',
                'MANDIRI'     => 'MANDIRI_VIRTUAL_ACCOUNT',
                'BCA'         => 'BCA_VIRTUAL_ACCOUNT',
                'BNI'         => 'BNI_VIRTUAL_ACCOUNT',
                'BRI'         => 'BRI_VIRTUAL_ACCOUNT',
                'PERMATA'     => 'PERMATA_VIRTUAL_ACCOUNT',
            ];

            $normalizedChannels = [];
            foreach ($params['allowed_payment_channels'] as $channel) {
                $normalizedChannels[] = $channelMap[strtoupper($channel)] ?? strtoupper($channel);
            }
            $payload['allowed_payment_channels'] = array_values(array_unique($normalizedChannels));
        }

        // Expiry date (due_date / expires_at)
        $expiresAt = $params['expires_at'] ?? $params['due_date'] ?? null;
        if (!empty($expiresAt)) {
            $ts = is_numeric($expiresAt) ? (int)$expiresAt : strtotime($expiresAt);
            if ($ts) {
                $payload['expires_at'] = date('c', $ts);
            } else {
                $payload['expires_at'] = $expiresAt;
            }
        }

        Log::info('Creating Xendit Payment Session', [
            'reference_id' => $referenceId,
            'amount'       => $amount,
        ]);

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->timeout(15)
                ->withHeaders([
                    'api-version'  => '2025-06-06',
                    'accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.xendit.co/sessions', $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Xendit Payment Session created successfully', [
                    'reference_id'     => $referenceId,
                    'session_id'       => $data['id'] ?? null,
                    'payment_link_url' => $data['payment_link_url'] ?? null,
                ]);

                $expiryDate = $data['expires_at'] ?? now()->addDay()->toIso8601String();
                $paymentUrl = $data['payment_link_url'] ?? null;
                $sessionId  = $data['payment_session_id'] ?? ($data['id'] ?? null);

                return [
                    'success'                      => true,
                    'id'                           => $sessionId,
                    'payment_session_id'           => $sessionId,
                    'user_id'                      => $data['business_id'] ?? $sessionId,
                    'reference_id'                 => $data['reference_id'] ?? $referenceId,
                    'external_id'                  => $data['reference_id'] ?? $referenceId,
                    'payment_link_url'             => $paymentUrl,
                    'invoice_url'                  => $paymentUrl,
                    'status'                       => $data['status'] ?? 'ACTIVE',
                    'expires_at'                   => $expiryDate,
                    'expiry_date'                  => $expiryDate,
                    'amount'                       => $data['amount'] ?? $amount,
                    'currency'                     => $data['currency'] ?? 'IDR',
                    'payer_email'                  => $customer['email'] ?? ($params['payer_email'] ?? null),
                    'description'                  => $payload['description'],
                    'merchant_name'                => 'DMC',
                    'merchant_profile_picture_url' => null,
                    'raw'                          => $data,
                ];
            }

            $errorBody = $response->body();
            Log::error('Failed to create Xendit Payment Session', [
                'status' => $response->status(),
                'body'   => $errorBody,
            ]);

            throw new Exception("Xendit API error ({$response->status()}): {$errorBody}");
        } catch (\Throwable $e) {
            Log::error('Exception while calling Xendit Payment Sessions API: ' . $e->getMessage(), [
                'reference_id' => $referenceId,
            ]);
            throw $e;
        }
    }

    /**
     * Format return URL to ensure it is a valid public HTTPS URL as strictly required by Xendit.
     *
     * @param string|null $url
     * @return string
     */
    public static function formatReturnUrl(?string $url): string
    {
        if (empty($url)) {
            return 'https://djakarta-miningclub.com/';
        }

        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        // If host is localhost, 127.0.0.1, or private IP, Xendit rejects it as invalid HTTPS URL.
        // Replace host with a valid public domain fallback (e.g. staging or production domain).
        if (empty($host) || in_array($host, ['localhost', '127.0.0.1', '0.0.0.0'], true) || filter_var($host, FILTER_VALIDATE_IP)) {
            $baseDomain = env('XENDIT_RETURN_BASE_URL', 'https://djakarta-miningclub.com');
            $path = $parsed['path'] ?? '/';
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            return rtrim($baseDomain, '/') . '/' . ltrim($path, '/') . $query;
        }

        // Ensure scheme is https (compatible with PHP 7.4)
        if (substr($url, 0, 7) === 'http://') {
            return 'https://' . substr($url, 7);
        }

        if (substr($url, 0, 8) !== 'https://') {
            return 'https://' . ltrim($url, '/');
        }

        return $url;
    }
}
