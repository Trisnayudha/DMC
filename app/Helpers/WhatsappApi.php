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


    public function WhatsappMessage()
    {
        try {
            $phone = $this->phone;
            $message = $this->message;
            $token = "7EoagVjJfYgElEkYI1KKXOObIzZoGB7S1QcDQbbOH6dqKNk6SL";

            // Melakukan pengecekan nomor telepon menggunakan endpoint check-number
            $checkUrl = 'https://nusagateway.com/api/check-number.php';
            $checkData = [
                'phone' => $phone,
                'token' => $token
            ];

            $checkResponse = $this->makeCurlRequest($checkUrl, 'POST', $checkData);

            if ($checkResponse['status'] === 'valid') {
                // Nomor telepon valid, lanjutkan proses pengiriman pesan WhatsApp

                $sendMessageUrl = 'https://nusagateway.com/api/send-message.php';
                $sendMessageData = [
                    'token' => $token,
                    'phone' => $phone,
                    'message' => $message
                ];

                $sendMessageResponse = $this->makeCurlRequest($sendMessageUrl, 'POST', $sendMessageData);


                return $this->res = 'valid';
            } else {
                // Nomor telepon tidak valid, kembalikan pesan error
                return $this->res = 'invalid';
            }
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }
    public function WhatsappMessageWithImage()
    {
        try {
            $phone = $this->phone;
            $caption = $this->caption;
            $token = "7EoagVjJfYgElEkYI1KKXOObIzZoGB7S1QcDQbbOH6dqKNk6SL";
            $image = "https://indonesiaminer.com" . $this->image;

            // Melakukan pengecekan nomor telepon menggunakan endpoint check-number
            $checkUrl = 'https://nusagateway.com/api/check-number.php';
            $checkData = [
                'phone' => $phone,
                'token' => $token
            ];

            $checkResponse = $this->makeCurlRequest($checkUrl, 'POST', $checkData);

            if ($checkResponse['status'] === 'valid') {
                // Nomor telepon valid, lanjutkan proses pengiriman pesan WhatsApp dengan gambar

                $sendMessageUrl = 'https://nusagateway.com/api/send-image.php';
                $sendMessageData = [
                    'token' => $token,
                    'phone' => $phone,
                    'caption' => $caption,
                    'image' => $image,
                ];

                $sendMessageResponse = $this->makeCurlRequest($sendMessageUrl, 'POST', $sendMessageData);

                return $this->res = $sendMessageResponse;
            } else {
                // Nomor telepon tidak valid, kembalikan pesan error
                return $this->res = 'invalid';
            }
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }

    public function WhatsappMessageWithDocument()
    {
        try {
            $phone = $this->phone;
            $document = $this->document;
            $token = "7EoagVjJfYgElEkYI1KKXOObIzZoGB7S1QcDQbbOH6dqKNk6SL";

            // Melakukan pengecekan nomor telepon menggunakan endpoint check-number
            $checkUrl = 'https://nusagateway.com/api/check-number.php';
            $checkData = [
                'phone' => $phone,
                'token' => $token
            ];

            $checkResponse = $this->makeCurlRequest($checkUrl, 'POST', $checkData);

            if ($checkResponse['status'] === 'valid') {
                // Nomor telepon valid, lanjutkan proses pengiriman pesan WhatsApp dengan dokumen

                $sendMessageUrl = 'https://nusagateway.com/api/send-document.php';
                $sendMessageData = [
                    'token' => $token,
                    'phone' => $phone,
                    'document' => $document,
                ];

                $sendMessageResponse = $this->makeCurlRequest($sendMessageUrl, 'POST', $sendMessageData);

                return $this->res = $sendMessageResponse;
            } else {
                // Nomor telepon tidak valid, kembalikan pesan error
                return $this->res = 'invalid';
            }
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }


    private function makeCurlRequest($url, $method, $data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);

        if ($response === false) {
            throw new \Exception(curl_error($ch));
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($statusCode !== 200) {
            throw new \Exception('Request failed with status code ' . $statusCode);
        }

        return json_decode($response, true);
    }
}
