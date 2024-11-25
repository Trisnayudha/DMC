<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorAdvertising extends Model
{
    use HasFactory;
    protected $table = 'sponsors_advertising';

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    // Accessor untuk memformat ukuran file
    public function getFormattedFileSizeAttribute()
    {
        $size = $this->file_size;

        if ($size >= 1048576) {
            $sizeFormatted = number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            $sizeFormatted = number_format($size / 1024, 2) . ' KB';
        } else {
            $sizeFormatted = $size . ' bytes';
        }

        return $sizeFormatted;
    }

    // Accessor untuk memformat tanggal (opsional)
    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('Y-m-d H:i:s') : null;
    }
}
