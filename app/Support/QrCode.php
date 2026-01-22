<?php

namespace App\Support;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class QrCode
{
    protected int $size = 200;

    protected string $format = 'svg';

    public static function format(string $format)
    {
        $instance = new static;
        $instance->format = strtolower($format); // png / svg / etc (ignored)
        return $instance;
    }

    public function size(int $size)
    {
        $this->size = $size;
        return $this;
    }

    public function errorCorrection(string $level)
    {
        // bacon v3: default EC is fine
        // method tetap ada biar API kompatibel
        return $this;
    }

    public function generate(string $text): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($this->size),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($text);
    }
}
