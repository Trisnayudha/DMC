<?php

namespace App\Jobs;

use App\Helpers\WhatsappApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phone;
    protected $message;
    protected $document;

    public function __construct($phone, $message, $document = null)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->document = $document;
    }

    public function handle()
    {
        $send = new WhatsappApi();
        $send->phone = $this->phone;
        $send->message = $this->message;

        if ($this->document) {
            $send->document = $this->document;
            $send->WhatsappMessageWithImage();
        } else {
            $send->WhatsappMessage();
        }
    }
}
