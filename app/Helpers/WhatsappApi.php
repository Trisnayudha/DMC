<?php

namespace App\Helpers;

class WhatsappApi
{
    public $phone;
    public $document;
    public $message;
    public $res;
    public $image;
    public $caption;

    protected $baseUrl = 'https://wa-gateway.indonesiaminer.com/api';
    protected $apiKey  = 'wg_8cdbbd2cf9818933e53792252a2892c70a2f5c1bc2c83e28';

    /**
     * Kirim pesan WA ke nomor biasa
     * Body: { to, text }
     */
    public function WhatsappMessage()
    {
        try {
            $to = $this->phone;
            $text = $this->message;

            $payload = [
                'to' => $to,
                'text' => $text,
            ];

            $response = $this->makeJsonRequest(
                $this->baseUrl . '/send',
                'POST',
                $payload
            );

            if (!empty($response['ok']) && $response['ok'] === true) {
                return $this->res = 'valid';
            }

            return $this->res = $response['message'] ?? 'failed';
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }

    /**
     * Kirim pesan ke group
     * phone diisi group id, misal: 1203630xxxxxxx@g.us
     * atau raw id yang nanti dinormalize oleh API
     */
    public function WhatsappMessageGroup()
    {
        try {
            $to = $this->phone;
            $text = $this->message;

            $payload = [
                'to' => $to,
                'text' => $text,
            ];

            $response = $this->makeJsonRequest(
                $this->baseUrl . '/send',
                'POST',
                $payload
            );

            if (!empty($response['ok']) && $response['ok'] === true) {
                return $this->res = 'valid';
            }

            return $this->res = $response['message'] ?? 'failed';
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }

    /**
     * Belum bisa dipakai karena API /send saat ini hanya support text
     */
    public function WhatsappMessageWithImage()
    {
        try {
            return $this->res = 'Image sending endpoint is not available yet in wa-gateway.';
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }

    /**
     * Belum bisa dipakai karena API /send saat ini hanya support text
     */
    public function WhatsappMessageWithDocument()
    {
        try {
            return $this->res = 'Document sending endpoint is not available yet in wa-gateway.';
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }

    private function makeJsonRequest($url, $method, $data = [])
    {
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-KEY: ' . $this->apiKey,
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);

        if ($response === false) {
            throw new \Exception(curl_error($ch));
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = $decoded['message'] ?? ('Request failed with status code ' . $statusCode);
            throw new \Exception($message);
        }

        return $decoded;
    }
}
