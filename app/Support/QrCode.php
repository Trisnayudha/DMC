<?php

namespace App\Support;

use BaconQrCode\Renderer\ImageRenderer;
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
        return $this;
    }

    public function generate(string $text): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($this->size),
            new \BaconQrCode\Renderer\Image\GdImageBackEnd()
        );

        $writer = new Writer($renderer);
        return $writer->writeString($text);
    }
}
