<?php

namespace App\Models\DigitalEdition;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalModel extends Model
{
    use HasFactory;
    protected $table = 'digital_edition';
    protected $fillable = [
        'link',
        'image',
        'sort',
    ];
}
