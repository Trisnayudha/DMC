<?php

namespace App\Models\Program;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramMedia extends Model
{
    protected $table = 'program_media';

    protected $fillable = [
        'program_id',
        'type',
        'file_path',
        'video_url',
        'caption',
        'sort'
    ];

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}
