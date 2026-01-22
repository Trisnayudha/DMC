<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\Png;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCode
{
    protected int $size = 200;

    public static function format(string $format)
    {
        return new static;
    }

    public function size(int $size)
    {
        $this->size = $size;
        return $this;
    }

    public function errorCorrection(string $level)
    {
        // bacon v2 default already OK
        return $this;
    }

    public function generate(string $text): string
    {
        $renderer = new Png(
            new RendererStyle($this->size)
        );

        $writer = new Writer($renderer);
        return $writer->writeString($text);
    }
}
