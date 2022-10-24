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
            $token = "FinMZc6DItk1r5EdYhUdlLcM3dmDWvBqFWldZbV7YQGoCTSQKh";

            $url = 'http://nusagateway.com/api/send-message.php';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array(
                'token'    => $token,
                'phone'     => $phone,
                'message'   => $message,
            ));
            $status = curl_exec($curl);
            curl_close($curl);
            return $this->res = $status;
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }
    public function WhatsappMessageWithImage()
    {
        try {
            $phone = $this->phone;
            $caption = $this->caption;
            $token = "FinMZc6DItk1r5EdYhUdlLcM3dmDWvBqFWldZbV7YQGoCTSQKh";
            $image = "https://indonesiaminer.com" . $this->image;

            $url = 'https://nusagateway.com/api/send-image.php';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array(
                'token'    => $token,
                'phone'     => $phone,
                'caption'   => $caption,
                'image'     => $image,
            ));
            $status = curl_exec($curl);
            curl_close($curl);
            return $this->res = $status;
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }

    public function WhatsappMessageWithDocument()
    {
        try {
            $phone = $this->phone;
            $document = $this->document;
            $token = "FinMZc6DItk1r5EdYhUdlLcM3dmDWvBqFWldZbV7YQGoCTSQKh";

            $url = 'https://nusagateway.com/api/send-document.php';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array(
                'token'    => $token,
                'phone'     => $phone,
                'document'   => $document,
            ));
            $status = curl_exec($curl);
            curl_close($curl);
            return $this->res = $status;
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }
}
