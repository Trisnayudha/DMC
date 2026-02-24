<?php

namespace App\Models\Program;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $table = 'programs';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'status',
        'published_at',
        'views_count',
        'created_by'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function media()
    {
        return $this->hasMany(ProgramMedia::class, 'program_id')->orderBy('sort', 'asc');
    }

    public function images()
    {
        return $this->hasMany(ProgramMedia::class, 'program_id')->where('type', 'image')->orderBy('sort', 'asc');
    }

    public function video()
    {
        return $this->hasOne(ProgramMedia::class, 'program_id')->where('type', 'video');
    }
}
